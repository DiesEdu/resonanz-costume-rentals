<?php

/**
 * Costume Rental – PHP REST API Entry Point
 *
 * Works with BOTH:
 *   • PHP built-in server:  php -S localhost:8000 index.php   (run inside backend/)
 *   • Apache + mod_rewrite: via .htaccess (RewriteRule sets ?url=)
 *
 * URL pattern:  /api/{resource}[/{id}[/{sub-action}]]
 *
 * Supported endpoints:
 *   GET    /api/costumes               – list costumes (?category=&search=)
 *   GET    /api/costumes/categories    – get category list
 *   GET    /api/costumes/{id}          – get single costume
 *   GET    /api/bookings               – list bookings (?email=)
 *   GET    /api/bookings/{id}          – get single booking
 *   POST   /api/bookings               – create booking (JSON)
 *   PUT    /api/bookings/{id}/cancel   – cancel booking
 *   PUT    /api/bookings/{id}/status   – update booking status (processing/completed)
 */

// ── CORS headers (update origin for production) ──────────────────────────────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Role');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Route parsing ─────────────────────────────────────────────────────────────
// Support both:
//  • Apache .htaccess sets $_GET['url']
//  • PHP built-in server: parse REQUEST_URI directly
if (isset($_GET['url'])) {
    // Apache path
    $rawUrl = $_GET['url'];
} else {
    // Built-in server path: strip leading /api/ from REQUEST_URI
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $requestUri = ltrim($requestUri, '/');
    // Remove leading "api/" prefix
    $rawUrl = preg_replace('#^api/#', '', $requestUri);
}

$url = trim($rawUrl, '/');
$segments = $url ? explode('/', $url) : [];

$resource = $segments[0] ?? '';      // "costumes" | "bookings" | "auth"
$idOrSub = $segments[1] ?? '';      // numeric id OR "categories" OR auth action
$subAction = $segments[2] ?? '';      // "cancel"

// Map URL segments to ?action= convention used inside api files
switch ($resource) {
    case 'auth':
        // /api/auth/register  →  ?action=register
        // /api/auth/login     →  ?action=login
        $_GET['action'] = $idOrSub ?: 'login';
        require __DIR__ . '/api/auth.php';
        break;

    case 'costumes':
        if ($idOrSub === 'categories') {
            $_GET['action'] = 'categories';
        } elseif (is_numeric($idOrSub)) {
            $_GET['action'] = 'get';
            $_GET['id'] = $idOrSub;
        } else {
            // list (GET) or create (POST) – handled inside costumes.php by HTTP method
            $_GET['action'] = 'list';
        }
        require __DIR__ . '/api/costumes.php';
        break;

    case 'bookings':
        if (is_numeric($idOrSub) && $subAction === 'cancel') {
            $_GET['action'] = 'cancel';
            $_GET['id'] = $idOrSub;
        } elseif (is_numeric($idOrSub) && $subAction === 'status') {
            $_GET['action'] = 'status';
            $_GET['id'] = $idOrSub;
        } elseif (is_numeric($idOrSub)) {
            $_GET['action'] = 'get';
            $_GET['id'] = $idOrSub;
        } else {
            // list (GET) or create (POST) – handled inside bookings.php by HTTP method
            $_GET['action'] = 'list';
        }
        require __DIR__ . '/api/bookings.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'error' => 'Not found',
            'message' => "Unknown resource: '{$resource}'",
        ]);
        break;
}
