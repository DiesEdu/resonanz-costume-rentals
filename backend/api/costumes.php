<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
// Load Drive helpers without running its router
if (!defined('DRIVE_LIBRARY_ONLY')) {
    define('DRIVE_LIBRARY_ONLY', true);
}
require_once __DIR__ . '/drive.php';

use CostumeRental\Middleware\AuthMiddleware;

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
        if ($action === 'get') {
            getCostume((int) ($_GET['id'] ?? 0));
        } else {
            listCostumes();
        }
        break;

    case 'POST':
        createCostume();
        break;

    case 'PUT':
        updateCostume();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// ─────────────────────────────────────────────

function getCostumesCount(string $category = '', string $search = ''): int
{
    $db = getDB();

    $sql = 'SELECT COUNT(DISTINCT c.id) as total
        FROM costumes c
        LEFT JOIN costume_stock cs ON cs.costume_id = c.id
        WHERE 1 = 1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND c.group_category = :category';
        $params[':category'] = $category;
    }

    if ($search) {
        $sql .= ' AND (c.name LIKE :search_name OR c.group_category LIKE :search_category)';
        $params[':search_name'] = '%' . $search . '%';
        $params[':search_category'] = '%' . $search . '%';
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

function getCostumesPaginated(int $limit, int $offset, string $category = '', string $search = ''): array
{
    $db = getDB();

    $sql = 'SELECT 
            c.id,
            c.name,
            c.costume_code,
            c.type,
            c.group_category,
            c.rack_id,
            c.image,
            cs.size,
            cs.gender,
            (COALESCE(cs.quantity, 0) - COALESCE(ob.total_booked, 0)) AS quantity
        FROM costumes c
        LEFT JOIN costume_stock cs ON cs.costume_id = c.id
        LEFT JOIN (
            SELECT costume_stock_id, SUM(amount_book) AS total_booked
            FROM bookings
            WHERE status IN ("waiting_approval","processing","completed")
            GROUP BY costume_stock_id
        ) ob ON ob.costume_stock_id = cs.id
        WHERE 1 = 1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND c.group_category = :category';
        $params[':category'] = $category;
    }

    if ($search) {
        $sql .= ' AND (c.name LIKE :search_name OR c.group_category LIKE :search_category)';
        $params[':search_name'] = '%' . $search . '%';
        $params[':search_category'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY c.id 
              LIMIT :limit OFFSET :offset';

    $stmt = $db->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listCostumes(): void
{
    // Get pagination parameters with validation
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : 20;

    $category = $_GET['category'] ?? '';
    $search = $_GET['search'] ?? '';

    // Get total count
    $total = getCostumesCount($category, $search);

    // Calculate pagination
    $totalPages = ceil($total / $perPage);
    $page = min($page, max($totalPages, 1)); // Ensure page doesn't exceed total pages
    $offset = ($page - 1) * $perPage;

    // Get paginated data
    $rows = getCostumesPaginated($perPage, $offset, $category, $search);

    // Group rows by costume and build sizes array
    $costumes = [];
    foreach ($rows as $row) {
        $costumeId = $row['id'];
        if (!isset($costumes[$costumeId])) {
            $costumes[$costumeId] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'costume_code' => $row['costume_code'],
                'type' => $row['type'],
                'group_category' => $row['group_category'],
                'rack_id' => $row['rack_id'],
                'image' => $row['image'],
                'sizes' => []
            ];
        }
        if ($row['size'] !== null) {
            $costumes[$costumeId]['sizes'][] = [
                'size' => $row['size'],
                'gender' => $row['gender'] ?? 'unisex',
                'quantity' => (int) $row['quantity']
            ];
        }
    }

    // Build response
    $response = [
        'data' => array_values($costumes),
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ];

    // Set response headers
    header('Content-Type: application/json');
    echo json_encode($response);
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
        'SELECT 
            c.id,
            c.name,
            c.costume_code,
            c.type,
            c.group_category,
            c.rack_id,
            c.image,
            cs.size,
            cs.gender,
            (COALESCE(cs.quantity, 0) - COALESCE(ob.total_booked, 0)) AS quantity
        FROM costumes c
        LEFT JOIN costume_stock cs ON cs.costume_id = c.id
        LEFT JOIN (
            SELECT costume_stock_id, SUM(amount_book) AS total_booked
            FROM bookings
            WHERE status IN ("waiting_approval","processing","completed")
            GROUP BY costume_stock_id
        ) ob ON ob.costume_stock_id = cs.id
        WHERE c.id = :id'
    );

    $stmt->execute([':id' => $id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        http_response_code(404);
        echo json_encode(['error' => 'Costume not found']);
        return;
    }

    $costume = [
        'id' => $rows[0]['id'],
        'name' => $rows[0]['name'],
        'costume_code' => $rows[0]['costume_code'],
        'type' => $rows[0]['type'],
        'group_category' => $rows[0]['group_category'],
        'rack_id' => $rows[0]['rack_id'],
        'image' => $rows[0]['image'],
        'sizes' => []
    ];

    foreach ($rows as $row) {
        if ($row['size'] !== null) {
            $costume['sizes'][] = [
                'size' => $row['size'],
                'gender' => $row['gender'] ?? 'unisex',
                'quantity' => (int) $row['quantity']
            ];
        }
    }

    echo json_encode(['data' => $costume]);
}

function createCostume(): void
{
    AuthMiddleware::requireAdminOrManager();

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isMultipart = str_contains($contentType, 'multipart/form-data');

    $body = $isMultipart ? $_POST : json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = [];
    }

    $name = trim($body['name'] ?? '');
    $costume_code = trim($body['costume_code'] ?? '');
    $type = trim($body['type'] ?? '');
    $group_category_id = trim($body['category'] ?? '');
    $description = trim($body['description'] ?? '');
    $rack_id = trim($body['rack_id'] ?? '0');
    $sizeStocks = $body['sizeStocks'] ?? [];

    $imagePath = trim($body['image'] ?? '');
    if ($isMultipart && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handleImageUpload($_FILES['image']);
        if (!$upload['ok']) {
            http_response_code(400);
            echo json_encode(['error' => $upload['error']]);
            return;
        }
        $imagePath = $upload['path'];
    }

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'name are required']);
        return;
    }
    if (!$type) {
        http_response_code(400);
        echo json_encode(['error' => 'type is required']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO costumes (name, costume_code, type, group_category, rack_id, image, description)
         VALUES (:name, :costume_code, :type, :group_category_id, :rack_id, :image, :description)'
    );
    $stmt->execute([
        ':name' => $name,
        ':costume_code' => $costume_code,
        ':type' => $type,
        ':group_category_id' => $group_category_id,
        ':rack_id' => $rack_id,
        ':image' => $imagePath,
        ':description' => $description,
    ]);

    $costumeId = (int) $db->lastInsertId();

    if (is_string($sizeStocks)) {
        $sizeStocks = stripslashes($sizeStocks); // 🔥 FIX
        $sizeStocks = json_decode($sizeStocks, true);
    }

    if (is_array($sizeStocks) && count($sizeStocks) > 0) {
        $sizeStmt = $db->prepare(
            'INSERT INTO costume_stock (costume_id, quantity, size, gender) VALUES (:costume_id, :quantity, :size, :gender)'
        );

        foreach ($sizeStocks as $item) {
            $size = trim($item['size'] ?? '');
            $stock = (int) ($item['stock'] ?? 0);
            $gender = $item['gender'] ?? 'unisex';

            if ($size !== '') {
                $sizeStmt->execute([
                    ':costume_id' => $costumeId,
                    ':quantity' => $stock,
                    ':size' => $size,
                    ':gender' => $gender,
                ]);
            }
        }
    }

    // Return the newly created costume
    getCostume($costumeId);
}

function updateCostume(): void
{
    AuthMiddleware::requireAdminOrManager();

    // Get costume ID from URL path or query parameter
    $costumeId = (int) ($_GET['id'] ?? 0);

    if ($costumeId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid costume ID']);
        return;
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isMultipart = str_contains($contentType, 'multipart/form-data');

    $body = $isMultipart ? $_POST : json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = [];
    }

    $name = trim($body['name'] ?? '');
    $costume_code = trim($body['costume_code'] ?? '');
    $type = trim($body['type'] ?? '');
    $group_category_id = trim($body['category'] ?? '');
    $rack_id = trim($body['rack_id'] ?? '0');
    $sizeStocks = $body['sizeStocks'] ?? [];

    $imagePath = trim($body['image'] ?? '');
    if ($isMultipart && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handleImageUpload($_FILES['image']);
        if (!$upload['ok']) {
            http_response_code(400);
            echo json_encode(['error' => $upload['error']]);
            return;
        }
        $imagePath = $upload['path'];
    }

    if (!$name) {
        http_response_code(400);
        echo json_encode(['error' => 'name are required']);
        return;
    }
    if (!$type) {
        http_response_code(400);
        echo json_encode(['error' => 'type is required']);
        return;
    }

    $db = getDB();

    // Check if costume exists
    $checkStmt = $db->prepare('SELECT id FROM costumes WHERE id = :id');
    $checkStmt->execute([':id' => $costumeId]);
    if (!$checkStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Costume not found']);
        return;
    }

    // Update costume
    $stmt = $db->prepare(
        'UPDATE costumes SET name = :name, costume_code = :costume_code, type = :type, group_category = :group_category_id, rack_id = :rack_id, image = :image WHERE id = :id'
    );
    $stmt->execute([
        ':name' => $name,
        ':costume_code' => $costume_code,
        ':type' => $type,
        ':group_category_id' => $group_category_id,
        ':rack_id' => $rack_id,
        ':image' => $imagePath,
        ':id' => $costumeId,
    ]);

    // Delete existing size stocks
    $deleteStmt = $db->prepare('DELETE FROM costume_stock WHERE costume_id = :costume_id');
    $deleteStmt->execute([':costume_id' => $costumeId]);

    // Insert new size stocks
    if (is_string($sizeStocks)) {
        $sizeStocks = stripslashes($sizeStocks);
        $sizeStocks = json_decode($sizeStocks, true);
    }

    if (is_array($sizeStocks) && count($sizeStocks) > 0) {
        $sizeStmt = $db->prepare(
            'INSERT INTO costume_stock (costume_id, quantity, size, gender) VALUES (:costume_id, :quantity, :size, :gender)'
        );

        foreach ($sizeStocks as $item) {
            $size = trim($item['size'] ?? '');
            $stock = (int) ($item['stock'] ?? 0);
            $gender = $item['gender'] ?? 'unisex';

            if ($size !== '') {
                $sizeStmt->execute([
                    ':costume_id' => $costumeId,
                    ':quantity' => $stock,
                    ':size' => $size,
                    ':gender' => $gender,
                ]);
            }
        }
    }

    // Return the updated costume
    getCostume($costumeId);
}

/**
 * Handle image upload and return public path.
 */
function handleImageUpload(array $file): array
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed. Please try again.'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'Image must be 5MB or smaller.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : mime_content_type($file['tmp_name']);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, or WebP images are allowed.'];
    }

    $targetName = uniqid('costume_', true) . '.' . $allowed[$mime];
    $folderId = getFolderId(); // Uses env GOOGLE_DRIVE_FOLDER_ID by default

    $driveResult = driveUploadFromArray($file, $targetName, $folderId);
    if (!$driveResult['ok']) {
        return ['ok' => false, 'error' => $driveResult['error']];
    }

    // Prefer storing Drive file ID (frontend builds thumbnail URL from it)
    $fileId = $driveResult['data']['id'] ?? null;
    if (!$fileId) {
        return ['ok' => false, 'error' => 'Drive upload succeeded but no file ID returned.'];
    }

    return ['ok' => true, 'path' => $fileId];
}

/**
 * Build absolute public URL for stored assets.
 */
function buildPublicPath(string $relative): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');

    return sprintf('%s://%s%s/%s', $scheme, $host, $base, ltrim($relative, '/'));
}

/**
 * Normalise a raw DB row into the shape expected by the frontend.
 */
function formatCostume(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'costume_code' => $row['costume_code'],
        'group_category' => $row['group_category'],
        'rack_id' => $row['rack_id'],
        'type' => $row['type'] ?? null,
        'sizes' => $row['sizes'] ?? '',
        'quantity' => max(0, (int) $row['quantity']),
        'image' => $row['image'],
    ];
}
