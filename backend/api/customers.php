<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use CostumeRental\Middleware\AuthMiddleware;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'get') {
            getCustomer((int) ($_GET['id'] ?? 0));
        } else {
            listCustomers();
        }
        break;
}

function getCustomer(int $id)
{
    AuthMiddleware::authenticate();

    $db = getDB();

    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);

    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
        return;
    }

    echo json_encode($customer);
}

function listCustomers()
{
    AuthMiddleware::authenticate();

    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM users');
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    for ($i = 0; $i < count($customers); $i++) {
        $customers[$i] = formatCustomers($customers[$i]);
    }
    echo json_encode($customers);
}

function formatCustomers(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
    ];
}

?>