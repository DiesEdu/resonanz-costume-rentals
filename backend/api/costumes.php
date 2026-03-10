<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Costumes API
 *
 * Routes handled (set by index.php via $_GET['action']):
 *   GET  action=list     - list all costumes (supports ?category=&search=)
 *   GET  action=get&id=  - get a single costume by id
 *   GET  action=categories - get distinct categories
 */

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'categories') {
            getCategories();
        } elseif ($action === 'get') {
            getCostume((int) ($_GET['id'] ?? 0));
        } else {
            listCostumes();
        }
        break;

    case 'POST':
        createCostume();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// ─────────────────────────────────────────────

function listCostumes(): void
{
    $db = getDB();
    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';

    $sql = 'SELECT c.*, GROUP_CONCAT(cs.size ORDER BY FIELD(cs.size,"XS","S","M","L","XL") SEPARATOR ",") AS sizes
               FROM costumes c
               LEFT JOIN costume_sizes cs ON cs.costume_id = c.id
               WHERE 1=1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND c.category = :category';
        $params[':category'] = $category;
    }

    if ($search) {
        $sql .= ' AND (c.name LIKE :search OR c.category LIKE :search OR c.description LIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $sql .= ' GROUP BY c.id ORDER BY c.id';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    echo json_encode(['data' => array_map('formatCostume', $rows)]);
}

function getCostume(int $id): void
{
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid id']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT c.*, GROUP_CONCAT(cs.size ORDER BY FIELD(cs.size,"XS","S","M","L","XL") SEPARATOR ",") AS sizes
         FROM costumes c
         LEFT JOIN costume_sizes cs ON cs.costume_id = c.id
         WHERE c.id = :id
         GROUP BY c.id'
    );
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Costume not found']);
        return;
    }

    echo json_encode(['data' => formatCostume($row)]);
}

function getCategories(): void
{
    $db = getDB();
    $stmt = $db->query('SELECT DISTINCT category FROM costumes ORDER BY category');
    $cats = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['data' => array_merge(['All'], $cats)]);
}

function createCostume(): void
{
    $body = json_decode(file_get_contents('php://input'), true);

    $name = trim($body['name'] ?? '');
    $category = trim($body['category'] ?? '');
    $container = trim($body['container'] ?? '');
    $description = trim($body['description'] ?? '');
    $image = trim($body['image'] ?? '');
    $available = isset($body['available']) ? (int) $body['available'] : 1;
    $sizes = $body['sizes'] ?? [];

    if (!$name || !$category) {
        http_response_code(400);
        echo json_encode(['error' => 'name and category are required']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO costumes (name, category, container, description, image, available)
         VALUES (:name, :category, :container, :description, :image, :available)'
    );
    $stmt->execute([
        ':name' => $name,
        ':category' => $category,
        ':container' => $body['container'] ?? '',
        ':description' => $description,
        ':image' => $image,
        ':available' => $available,
    ]);

    $costumeId = (int) $db->lastInsertId();

    // Insert sizes
    if (!empty($sizes)) {
        $sizeStmt = $db->prepare(
            'INSERT IGNORE INTO costume_sizes (costume_id, size) VALUES (:costume_id, :size)'
        );
        foreach ($sizes as $size) {
            $sizeStmt->execute([':costume_id' => $costumeId, ':size' => $size]);
        }
    }

    // Return the newly created costume
    getCostume($costumeId);
}

/**
 * Normalise a raw DB row into the shape expected by the frontend.
 */
function formatCostume(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'category' => $row['category'],
        'container' => $row['container'],
        'size' => $row['sizes'] ? explode(',', $row['sizes']) : [],
        'description' => $row['description'],
        'image' => $row['image'],
        'available' => (bool) $row['available'],
        'rating' => (float) $row['rating'],
        'reviews' => (int) $row['reviews'],
    ];
}
