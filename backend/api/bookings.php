<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use SheetMusic\Middleware\AuthMiddleware;

/**
 * Bookings API
 *
 * Routes handled (set by index.php via $_GET['action']):
 *   GET    action=list              - list all bookings (optionally ?email= to filter by user)
 *   GET    action=get&id=           - get a single booking by id
 *   POST   action=create            - create a new booking  (JSON body)
 *   PUT    action=cancel&id=        - cancel a booking (customer)
 *   PUT    action=status&id=        - update booking status (management / admin)
 */

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'get') {
            getBooking((int) ($_GET['id'] ?? 0));
        } elseif ($action === 'list-admin') {
            listBookingsForManager();
        } else {
            listBookings();
        }
        break;

    case 'POST':
        createBooking();
        break;

    case 'PUT':
        if ($action === 'cancel') {
            cancelBooking((int) ($_GET['id'] ?? 0));
        } elseif ($action === 'status') {
            updateBookingStatus((int) ($_GET['id'] ?? 0));
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// ─────────────────────────────────────────────

function listBookings(): void
{
    $user = AuthMiddleware::authenticate();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    $db = getDB();
    $customerId = $user['id'] ?? 0;

    $sql = 'SELECT b.*, c.image AS costume_image, c.name AS costume_name, c.size AS costume_size
            FROM bookings b
            LEFT JOIN costumes c ON c.id = b.costume_id
            WHERE 1=1';
    $params = [];

    if ($customerId > 0) {
        $sql .= ' AND b.customer_id = :customer_id';
        $params[':customer_id'] = $customerId;
    }

    $sql .= ' ORDER BY b.booking_date DESC, b.id DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['data' => array_map('formatBooking', $stmt->fetchAll())]);
}

function listBookingsForManager(): void
{
    $user = AuthMiddleware::authenticate();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    $db = getDB();

    $sql = 'SELECT b.*, c.image AS costume_image, c.name AS costume_name, c.size AS costume_size
            FROM bookings b
            LEFT JOIN costumes c ON c.id = b.costume_id
            WHERE 1=1';
    $params = [];

    $sql .= ' ORDER BY b.booking_date DESC, b.id DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['data' => array_map('formatBooking', $stmt->fetchAll())]);
}

function getBooking(int $id): void
{
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT b.*, c.image AS costume_image
         FROM bookings b
         LEFT JOIN costumes c ON c.id = b.costume_id
         WHERE b.id = :id'
    );
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        return;
    }

    echo json_encode(['data' => formatBooking($row)]);
}

function createBooking(): void
{
    $user = AuthMiddleware::authenticate();

    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    // Required fields validation
    $required = ['costumeId', 'customerId', 'startDate', 'endDate', 'size', 'amount'];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            http_response_code(422);
            echo json_encode(['error' => "Field '{$field}' is required"]);
            return;
        }
    }

    // Date validation
    $start = DateTime::createFromFormat('Y-m-d', $body['startDate']);
    $end = DateTime::createFromFormat('Y-m-d', $body['endDate']);
    if (!$start || !$end || $end <= $start) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid date range: endDate must be after startDate']);
        return;
    }

    $db = getDB();

    // Check costume availability
    $costumeId = (int) $body['costumeId'];
    $requestedAmount = (int) $body['amount'];
    $startDate = $body['startDate'];
    $endDate = $body['endDate'];

    // Get total stock quantity for the costume
    $stockStmt = $db->prepare('SELECT quantity FROM costume_stock WHERE costume_id = :costume_id');
    $stockStmt->execute([':costume_id' => $costumeId]);
    $stockRow = $stockStmt->fetch();

    if (!$stockRow) {
        http_response_code(422);
        echo json_encode(['error' => 'Costume stock not found']);
        return;
    }

    $totalStock = (int) $stockRow['quantity'];

    // Calculate total booked amount for overlapping dates
    // Overlap occurs when: (new_start <= existing_end) AND (new_end >= existing_start)
    $bookedStmt = $db->prepare('
        SELECT COALESCE(SUM(amount_book), 0) AS total_booked
        FROM bookings
        WHERE costume_id = :costume_id
        AND status NOT IN ("cancelled", "completed")
        AND start_date <= :end_date
        AND end_date >= :start_date
    ');
    $bookedStmt->execute([
        ':costume_id' => $costumeId,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);
    $bookedRow = $bookedStmt->fetch();
    $totalBooked = (int) $bookedRow['total_booked'];

    $availableAmount = $totalStock - $totalBooked;

    if ($requestedAmount > $availableAmount) {
        http_response_code(422);
        echo json_encode([
            'error' => 'Insufficient stock available',
            'available' => $availableAmount,
            'requested' => $requestedAmount,
            'total_stock' => $totalStock
        ]);
        return;
    }

    $stmt = $db->prepare(
        'INSERT INTO bookings
            (costume_id, customer_id, start_date, end_date, amount_book, status, booking_date)
         VALUES
            (:costume_id, :customer_id, :start_date, :end_date, :amount, "waiting_approval", CURDATE())'
    );

    $stmt->execute([
        ':costume_id' => (int) $body['costumeId'],
        ':customer_id' => (int) $body['customerId'],
        ':start_date' => $body['startDate'],
        ':end_date' => $body['endDate'],
        ':amount' => (int) $body['amount'],
    ]);

    $newId = (int) $db->lastInsertId();

    $stmt2 = $db->prepare(
        'SELECT b.*, c.image AS costume_image
         FROM bookings b
         LEFT JOIN costumes c ON c.id = b.costume_id
         WHERE b.id = :id'
    );
    $stmt2->execute([':id' => $newId]);

    http_response_code(201);
    echo json_encode(['data' => formatBooking($stmt2->fetch())]);
}

function cancelBooking(int $id): void
{
    $user = AuthMiddleware::authenticate();

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT b.*, c.image AS costume_image
         FROM bookings b
         LEFT JOIN costumes c ON c.id = b.costume_id
         WHERE b.id = :id'
    );
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        return;
    }

    if ($existing['status'] === 'cancelled') {
        http_response_code(400);
        echo json_encode(['error' => 'Booking is already cancelled']);
        return;
    }

    if ($existing['status'] === 'completed') {
        http_response_code(400);
        echo json_encode(['error' => 'Completed bookings cannot be cancelled']);
        return;
    }

    if ((int) $existing['customer_id'] !== (int) ($user['id'] ?? 0)) {
        http_response_code(403);
        echo json_encode(['error' => 'You can only cancel your own bookings']);
        return;
    }

    $db->prepare('UPDATE bookings SET status = "cancelled" WHERE id = :id')
        ->execute([':id' => $id]);

    $stmt->execute([':id' => $id]);
    echo json_encode(['data' => formatBooking($stmt->fetch())]);
}

function updateBookingStatus(int $id): void
{
    AuthMiddleware::requireAdminOrManager();

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        return;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $newStatus = $body['status'] ?? '';

    $validStatuses = ['waiting_approval', 'processing', 'completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses, true)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid status value']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT b.*, c.image AS costume_image
         FROM bookings b
         LEFT JOIN costumes c ON c.id = b.costume_id
         WHERE b.id = :id'
    );
    $stmt->execute([':id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        return;
    }

    // Enforce allowed transitions
    $transitions = [
        'waiting_approval' => ['processing', 'cancelled'],
        'processing' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    $current = $existing['status'];
    if (!in_array($newStatus, $transitions[$current] ?? [], true)) {
        http_response_code(422);
        echo json_encode([
            'error' => "Invalid transition from '{$current}' to '{$newStatus}'",
        ]);
        return;
    }

    $db->prepare('UPDATE bookings SET status = :status WHERE id = :id')
        ->execute([':status' => $newStatus, ':id' => $id]);

    $stmt->execute([':id' => $id]);
    echo json_encode(['data' => formatBooking($stmt->fetch())]);
}

/**
 * Normalise a raw DB row into the shape expected by the frontend.
 */
function formatBooking(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'costumeId' => (int) $row['costume_id'],
        'customerId' => (int) $row['customer_id'],
        'startDate' => $row['start_date'],
        'endDate' => $row['end_date'],
        'amount' => (int) $row['amount_book'],
        'status' => $row['status'],
        'bookingDate' => $row['booking_date'],
        'costumeImage' => $row['costume_image'] ?? null,
        'costumeName' => $row['costume_name'] ?? null,
        'costumeSize' => $row['costume_size'] ?? null,
    ];
}
