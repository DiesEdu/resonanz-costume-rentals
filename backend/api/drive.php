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
 * POST /api/drive/files/batch
 * - form-data with file: Excel file containing 'fileName' column
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

    case 'POST':
        if ($action === 'files/batch') {
            batchFetchDriveFiles();
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
    $apiKey = validateApiKey();
    if (!$apiKey)
        return;

    $name = trim($_GET['name'] ?? '');
    $folder = getFolderId();

    if ($name === '') {
        http_response_code(400);
        echo json_encode(['error' => "Query parameter 'name' is required"]);
        return;
    }

    $result = searchDriveFile($apiKey, $name, $folder);

    if (!$result['ok']) {
        http_response_code($result['status']);
        echo json_encode(['error' => $result['error']]);
        return;
    }

    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo $result['body'];
}

function batchFetchDriveFiles(): void
{
    // Allow longer processing for multiple Drive lookups (default is 30s)
    // but keep a sane upper bound to avoid runaway scripts.
    @set_time_limit(300);

    $apiKey = validateApiKey();
    if (!$apiKey)
        return;

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');

        // Common client-side mistake: forcing Content-Type without boundary breaks PHP's file parser.
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $boundaryMissing = str_starts_with($contentType, 'multipart/form-data')
            && !str_contains($contentType, 'boundary=');

        $hint = $boundaryMissing
            ? ' (boundary missing - let Postman/cURL set Content-Type automatically when using form-data)'
            : '';

        echo json_encode(['error' => 'Excel/CSV file is required' . $hint]);
        return;
    }

    $folder = getFolderId();
    $uploadedFile = $_FILES['file'];

    // Validate file type
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Please upload Excel (.xlsx, .xls) or CSV file']);
        return;
    }

    try {
        // Read file names from Excel/CSV
        $fileNames = extractFileNamesFromFile($uploadedFile['tmp_name'], $fileExtension);

        // Guardrail: avoid huge batches that would exceed execution time
        $maxBatch = 200;
        if (count($fileNames) > $maxBatch) {
            $fileNames = array_slice($fileNames, 0, $maxBatch);
        }

        if (empty($fileNames)) {
            http_response_code(400);
            echo json_encode(['error' => 'No file names found in the uploaded file']);
            return;
        }

        // Search for each file name
        $results = [];
        foreach ($fileNames as $fileName) {
            $searchResult = searchDriveFile($apiKey, $fileName, $folder);

            if ($searchResult['ok']) {
                $driveResponse = json_decode($searchResult['body'], true);
                $results[$fileName] = [
                    'success' => true,
                    'files' => $driveResponse['files'] ?? []
                ];
            } else {
                $results[$fileName] = [
                    'success' => false,
                    'error' => $searchResult['error']
                ];
            }
        }

        // Clean up temp file
        unlink($uploadedFile['tmp_name']);

        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'total_processed' => count($results),
            'results' => $results
        ], JSON_PRETTY_PRINT);

    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Error processing file: ' . $e->getMessage()]);
    }
}

function extractFileNamesFromFile(string $filePath, string $extension): array
{
    $fileNames = [];

    if ($extension === 'csv') {
        // Handle CSV files
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Fix for PHP 8.4+ deprecation - explicitly set escape parameter
            $headers = fgetcsv($handle, 0, ',', '"', '\\'); // Added escape parameter

            // Find column index for fileName (case insensitive)
            $fileNameColumn = findColumnIndex($headers, ['filename', 'file name', 'name', 'file', 'image']);

            if ($fileNameColumn === -1) {
                fclose($handle);
                throw new Exception('Could not find fileName column. Expected column names: fileName, file name, name, file, or image');
            }

            while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) { // Added escape parameter
                if (isset($data[$fileNameColumn]) && !empty(trim($data[$fileNameColumn]))) {
                    $fileNames[] = trim($data[$fileNameColumn]);
                }
            }
            fclose($handle);
        }
    } else {
        // Handle Excel files
        requireFileReader();

        if ($extension === 'xlsx') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        } else {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }

        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new Exception('Excel file is empty');
        }

        $headers = array_shift($rows);

        // Find column index for fileName (case insensitive)
        $fileNameColumn = findColumnIndex($headers, ['filename', 'file name', 'name', 'file', 'image']);

        if ($fileNameColumn === -1) {
            throw new Exception('Could not find fileName column. Expected column names: fileName, file name, name, file, or image');
        }

        foreach ($rows as $row) {
            if (isset($row[$fileNameColumn]) && !empty(trim($row[$fileNameColumn]))) {
                $fileNames[] = trim($row[$fileNameColumn]);
            }
        }
    }

    return array_values(array_unique($fileNames)); // array_values to reset indices
}

function findColumnIndex(array $headers, array $possibleNames): int
{
    foreach ($headers as $index => $header) {
        if (is_string($header)) {
            $normalizedHeader = strtolower(trim($header));
            foreach ($possibleNames as $possibleName) {
                if ($normalizedHeader === strtolower($possibleName)) {
                    return $index;
                }
            }
        }
    }
    return -1;
}

function requireFileReader(): void
{
    // Check if PhpSpreadsheet is installed via Composer
    $autoloadPaths = [
        __DIR__ . '/../../../vendor/autoload.php', // Project root vendor
        __DIR__ . '/../vendor/autoload.php',       // Backend vendor
    ];

    $loaded = false;
    foreach ($autoloadPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $loaded = true;
            break;
        }
    }

    if (!$loaded || !class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
        throw new Exception('PhpSpreadsheet library is required. Please run: composer require phpoffice/phpspreadsheet');
    }
}

function validateApiKey(): ?string
{
    $apiKey = getenv('GOOGLE_API_KEY') ?: '';
    if ($apiKey === '') {
        http_response_code(500);
        echo json_encode(['error' => 'GOOGLE_API_KEY is not configured on the server']);
        return null;
    }
    return $apiKey;
}

function getFolderId(): string
{
    $folder = trim($_GET['folder'] ?? '');
    if ($folder === '') {
        $folder = trim(getenv('GOOGLE_DRIVE_FOLDER_ID') ?: '');
    }
    return $folder;
}

function searchDriveFile(string $apiKey, string $fileName, string $folder): array
{
    $escapedName = addcslashes($fileName, "'\\");
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

    $fields = trim($_GET['fields'] ?? 'files(id,name,size,mimeType,webViewLink,iconLink)');

    // Shared Drive support (also safe for My Drive)
    $driveId = trim($_GET['driveId'] ?? '');
    $useSharedDrive = $driveId !== '';

    $params = [
        'q' => implode(' and ', $queryParts),
        'fields' => $fields,
        'pageSize' => $pageSize,
        'spaces' => 'drive',
        'includeItemsFromAllDrives' => 'true',
        'supportsAllDrives' => 'true',
        'key' => $apiKey,
    ];

    if ($useSharedDrive) {
        $params['corpora'] = 'drive';
        $params['driveId'] = $driveId;
    } else {
        $params['corpora'] = 'user';
    }

    $url = 'https://www.googleapis.com/drive/v3/files?' . http_build_query($params);

    return driveHttpGet($url);
}

/**
 * Thin cURL wrapper (with sensible defaults and clear errors).
 */
/**
 * Thin cURL wrapper (with sensible defaults and clear errors).
 */
function driveHttpGet(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 12,          // limit per-request wait
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => true, // Added for security
        CURLOPT_USERAGENT => 'Costume-Rental-App/1.0', // Added for identification
    ]);

    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    // curl_close is deprecated in PHP 8.5+ - we can let the handle go out of scope
    // The handle will be automatically cleaned up by PHP's garbage collector

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
            // Try to parse Google's error response
            $errorData = json_decode($body, true);
            if (isset($errorData['error']['message'])) {
                $message .= ' - ' . $errorData['error']['message'];
            } else {
                $message .= ' - ' . substr($body, 0, 200); // Limit long responses
            }
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


