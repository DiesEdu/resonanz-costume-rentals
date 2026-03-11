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
    $category = trim($body['category'] ?? '');
    $container = trim($body['container'] ?? '');
    $amount = trim($body['amount'] ?? '');
    $description = trim($body['description'] ?? '');
    $available = isset($body['available']) ? (int) $body['available'] : 1;

    $sizes = $body['sizes'] ?? [];
    if ($isMultipart && isset($_POST['sizes'])) {
        if (is_array($_POST['sizes'])) {
            $sizes = $_POST['sizes'];
        } elseif (is_string($_POST['sizes'])) {
            $decoded = json_decode($_POST['sizes'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $sizes = $decoded;
            }
        }
    } elseif (is_string($sizes)) {
        $decoded = json_decode($sizes, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $sizes = $decoded;
        }
    }
    if (!is_array($sizes)) {
        $sizes = [];
    }

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

    if (!$name || !$category) {
        http_response_code(400);
        echo json_encode(['error' => 'name and category are required']);
        return;
    }

    $db = getDB();
    $stmt = $db->prepare(
        'INSERT INTO costumes (name, costume_code, category, container, amount, description, image, available)
         VALUES (:name, :costume_code, :category, :container, :amount, :description, :image, :available)'
    );
    $stmt->execute([
        ':name' => $name,
        ':costume_code' => $costume_code,
        ':category' => $category,
        ':container' => $container,
        ':amount' => (int) $amount,
        ':description' => $description,
        ':image' => $imagePath,
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
        'category' => $row['category'],
        'container' => $row['container'],
        'amount' => (int) $row['amount'],
        'size' => $row['sizes'] ? explode(',', $row['sizes']) : [],
        'description' => $row['description'],
        'image' => $row['image'],
        'available' => (bool) $row['available'],
        'rating' => (float) $row['rating'],
        'reviews' => (int) $row['reviews'],
    ];
}
