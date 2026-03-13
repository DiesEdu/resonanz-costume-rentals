<?php

require_once __DIR__ . '/../config/database.php'; // loads .env so GOOGLE_API_KEY is available

/**
 * Google Drive proxy API
 *
 * GET /api/drive/files?name={fileName}&folder={folderId}&fields={fields}&limit={n}
 * - name   : required. File name to match exactly.
 * - folder : optional. Restrict search to a specific folder id ("... in parents").
 * - fields : optional. Defaults to basic file metadata.
 * - limit  : optional. pageSize for Drive API (max 1000, defaults to 10).
 *
 * Uses env GOOGLE_API_KEY (set in backend/.env). Response is a thin wrapper
 * around the Drive v3 `files` list endpoint.
 */

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'files';

switch ($method) {
    case 'GET':
        if ($action === 'files') {
            fetchDriveFiles();
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Unknown action']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// ---------------------------------------------------------------------------

function fetchDriveFiles(): void
{
    $apiKey = getenv('GOOGLE_API_KEY') ?: '';
    if ($apiKey === '') {
        http_response_code(500);
        echo json_encode(['error' => 'GOOGLE_API_KEY is not configured on the server']);
        return;
    }

    $name = trim($_GET['name'] ?? '');
    // Folder ID can come from query or default env GOOGLE_DRIVE_FOLDER_ID
    $folder = trim($_GET['folder'] ?? '');
    if ($folder === '') {
        $folder = trim(getenv('GOOGLE_DRIVE_FOLDER_ID') ?: '');
    }
    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => "Query parameter 'name' is required"]);
        return;
    }

    $escapedName = addcslashes($name, "'\\");
    $queryParts = ["name = '$escapedName'"];
    if ($folder !== '') {
        $queryParts[] = "'$folder' in parents";
    }

    $pageSize = (int) ($_GET['limit'] ?? 10);
    if ($pageSize <= 0) {
        $pageSize = 10;
    } elseif ($pageSize > 1000) {
        $pageSize = 1000;
    }

    $fields = trim($_GET['fields'] ?? 'files(id,name,size)');

    $params = [
        'q' => implode(' and ', $queryParts),
        'fields' => $fields,
        'pageSize' => $pageSize,
        'key' => $apiKey,
    ];

    $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

    $response = driveHttpGet($url);
    if (!$response['ok']) {
        http_response_code($response['status']);
        echo json_encode(['error' => $response['error']]);
        return;
    }

    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo $response['body'];
}

/**
 * Thin cURL wrapper (with sensible defaults and clear errors).
 */
function driveHttpGet(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    // curl_close is deprecated in PHP 8.5; letting the handle go out of scope is enough.

    if ($errno !== 0) {
        return [
            'ok' => false,
            'status' => 502,
            'error' => 'Failed to reach Google Drive: ' . $error,
        ];
    }

    if ($status >= 400) {
        $message = 'Google Drive API returned HTTP ' . $status;
        if ($body) {
            $message .= ' - ' . $body;
        }
        return [
            'ok' => false,
            'status' => $status,
            'error' => $message,
        ];
    }

    return [
        'ok' => true,
        'status' => $status,
        'body' => $body ?: '{}',
    ];
}
