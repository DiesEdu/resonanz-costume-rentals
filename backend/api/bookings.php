<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Bookings API
 *
 * Routes handled (set by index.php via $_GET['action']):
 *   GET    action=list              - list all bookings (optionally ?email= to filter by user)
 *   GET    action=get&id=           - get a single booking by id
 *   POST   action=create            - create a new booking  (JSON body)
 *   PUT    action=cancel&id=        - cancel a booking
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

    $sql = 'SELECT * FROM bookings WHERE 1=1';
    $params = [];

    if ($email) {
        $sql .= ' AND email = :email';
        $params[':email'] = $email;
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
    $required = ['costumeId', 'costumeName', 'customerName', 'email', 'phone', 'startDate', 'endDate', 'size', 'totalPrice'];
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
    $stmt = $db->prepare(
        'INSERT INTO bookings
            (costume_id, costume_name, costume_image, customer_name, email, phone,
             start_date, end_date, size, total_price, status, booking_date)
         VALUES
            (:costume_id, :costume_name, :costume_image, :customer_name, :email, :phone,
             :start_date, :end_date, :size, :total_price, "pending", CURDATE())'
    );

    $stmt->execute([
        ':costume_id' => (int) $body['costumeId'],
        ':costume_name' => $body['costumeName'],
        ':costume_image' => $body['costumeImage'] ?? null,
        ':customer_name' => $body['customerName'],
        ':email' => $body['email'],
        ':phone' => $body['phone'],
        ':start_date' => $body['startDate'],
        ':end_date' => $body['endDate'],
        ':size' => $body['size'],
        ':total_price' => (float) $body['totalPrice'],
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

    $db->prepare('UPDATE bookings SET status = "cancelled" WHERE id = :id')
        ->execute([':id' => $id]);

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
        'costumeName' => $row['costume_name'],
        'costumeImage' => $row['costume_image'],
        'customerName' => $row['customer_name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'startDate' => $row['start_date'],
        'endDate' => $row['end_date'],
        'size' => $row['size'],
        'totalPrice' => (float) $row['total_price'],
        'status' => $row['status'],
        'bookingDate' => $row['booking_date'],
    ];
}
