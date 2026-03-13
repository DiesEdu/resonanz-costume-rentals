<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use SheetMusic\Middleware\AuthMiddleware;

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

    $sql = 'SELECT c.*, COALESCE(SUM(cs.quantity),0) AS quantity
        FROM costumes c
        LEFT JOIN costume_stock cs ON cs.costume_id = c.id
        WHERE 1 = 1';
    $params = [];

    if ($category && $category !== 'All') {
        $sql .= ' AND c.group_category = :category';
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
        'SELECT c.*, COALESCE(SUM(cs.quantity),0) AS quantity
        FROM costumes c
        LEFT JOIN costume_stock cs ON cs.costume_id = c.id
        WHERE c.id = :id
        GROUP BY c.id;'
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
    $group_category_id = trim($body['group_category_id'] ?? '');
    $rack_id = trim($body['rack_id'] ?? '');
    $size = trim($body['size'] ?? '');

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

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO costumes (name, costume_code, group_category_id, rack_id, size, image)
         VALUES (:name, :costume_code, :group_category_id, :rack_id, :size, :image)'
    );
    $stmt->execute([
        ':name' => $name,
        ':costume_code' => $costume_code,
        ':group_category_id' => $group_category_id,
        ':rack_id' => $rack_id,
        ':size' => $size,
        ':image' => $imagePath,
    ]);

    $costumeId = (int) $db->lastInsertId();

    // Return the newly created costume
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

    $uploadDir = __DIR__ . '/../upload/img';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        return ['ok' => false, 'error' => 'Unable to create upload directory.'];
    }

    $filename = uniqid('costume_', true) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['ok' => false, 'error' => 'Failed to save uploaded file.'];
    }

    $relative = 'upload/img/' . $filename;
    return ['ok' => true, 'path' => buildPublicPath($relative)];
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
        'size' => $row['size'],
        'quantity' => $row['quantity'],
        'image' => $row['image'],
    ];
}
