<?php

require_once __DIR__ . '/../config/database.php';

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
    $db = getDB();
    $email = $_GET['email'] ?? '';
    $customerId = isset($_GET['customerId']) ? (int) $_GET['customerId'] : 0;

    $sql = 'SELECT * FROM bookings WHERE 1=1';
    $params = [];

    if ($email) {
        $sql .= ' AND email = :email';
        $params[':email'] = $email;
    }

    if ($customerId > 0) {
        $sql .= ' AND customer_id = :customer_id';
        $params[':customer_id'] = $customerId;
    }

    $sql .= ' ORDER BY booking_date DESC, id DESC';

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
    $stmt = $db->prepare('SELECT * FROM bookings WHERE id = :id');
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
    $body = json_decode(file_get_contents('php://input'), true);

    if (!$body) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON body']);
        return;
    }

    // Required fields validation
    $required = ['costumeId', 'customerId', 'startDate', 'endDate', 'size'];
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

    // Ensure customer exists before proceeding
    $customerId = (int) $body['customerId'];
    $customerStmt = $db->prepare('SELECT id FROM users WHERE id = :id');
    $customerStmt->execute([':id' => $customerId]);
    if (!$customerStmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
        return;
    }

    $stmt = $db->prepare(
        'INSERT INTO bookings
            (costume_id, customer_id, start_date, end_date, size, status, booking_date)
         VALUES
            (:costume_id, :customer_id, :start_date, :end_date, :size, "waiting_approval", CURDATE())'
    );

    $stmt->execute([
        ':costume_id' => (int) $body['costumeId'],
        ':customer_id' => (int) $body['customerId'],
        ':start_date' => $body['startDate'],
        ':end_date' => $body['endDate'],
        ':size' => $body['size'],
    ]);

    $newId = (int) $db->lastInsertId();

    $stmt2 = $db->prepare('SELECT * FROM bookings WHERE id = :id');
    $stmt2->execute([':id' => $newId]);

    http_response_code(201);
    echo json_encode(['data' => formatBooking($stmt2->fetch())]);
}

function cancelBooking(int $id): void
{
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM bookings WHERE id = :id');
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

    $db->prepare('UPDATE bookings SET status = "cancelled" WHERE id = :id')
        ->execute([':id' => $id]);

    $stmt->execute([':id' => $id]);
    echo json_encode(['data' => formatBooking($stmt->fetch())]);
}

function updateBookingStatus(int $id): void
{
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
    $stmt = $db->prepare('SELECT * FROM bookings WHERE id = :id');
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

    // Role guard: only management/admin can move forward in the flow
    if (in_array($newStatus, ['processing', 'completed'], true)) {
        requireRole(['costume_management', 'admin']);
    }

    $db->prepare('UPDATE bookings SET status = :status WHERE id = :id')
        ->execute([':status' => $newStatus, ':id' => $id]);

    $stmt->execute([':id' => $id]);
    echo json_encode(['data' => formatBooking($stmt->fetch())]);
}

function requireRole(array $allowedRoles): void
{
    $role = $_SERVER['HTTP_X_ROLE'] ?? '';
    if (!$role || !in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Forbidden: requires role ' . implode(' or ', $allowedRoles),
        ]);
        exit;
    }
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
        'size' => $row['size'],
        'status' => $row['status'],
        'bookingDate' => $row['booking_date'],
    ];
}
