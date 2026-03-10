<?php

/**
 * Auth API  –  /api/auth/{action}
 *
 * POST /api/auth/register   – create new user account
 * POST /api/auth/login      – authenticate and return user data + token
 * POST /api/auth/logout     – (stateless; handled client-side)
 */

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ── Helpers ────────────────────────────────────────────────────────────────────

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function generateToken(int $userId): string
{
    // Simple signed token: base64(userId|timestamp|hash)
    $secret = defined('APP_SECRET') ? APP_SECRET : 'costume_rental_secret_2024';
    $payload = $userId . '|' . time();
    $sig = hash_hmac('sha256', $payload, $secret);
    return base64_encode($payload . '|' . $sig);
}

// ── Register ───────────────────────────────────────────────────────────────────

if ($action === 'register') {
    if ($method !== 'POST') {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $body = getBody();
    $name = trim($body['name'] ?? '');
    $email = trim($body['email'] ?? '');
    $phone = trim($body['phone'] ?? '');
    $password = $body['password'] ?? '';

    // Basic validation
    $errors = [];
    if (!$name)
        $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Valid email is required.';
    if (strlen($password) < 8)
        $errors[] = 'Password must be at least 8 characters.';

    if ($errors) {
        jsonResponse(['error' => implode(' ', $errors)], 422);
    }

    $pdo = getDB();

    // Check duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'An account with this email already exists.'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $phone, $hash]);
    $userId = (int) $pdo->lastInsertId();

    $token = generateToken($userId);

    jsonResponse([
        'message' => 'Account created successfully.',
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        ],
    ], 201);
}

// ── Login ──────────────────────────────────────────────────────────────────────

if ($action === 'login') {
    if ($method !== 'POST') {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $body = getBody();
    $email = trim($body['email'] ?? '');
    $password = $body['password'] ?? '';

    if (!$email || !$password) {
        jsonResponse(['error' => 'Email and password are required.'], 422);
    }

    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        jsonResponse(['error' => 'Invalid email or password.'], 401);
    }

    $token = generateToken((int) $user['id']);

    jsonResponse([
        'message' => 'Login successful.',
        'token' => $token,
        'user' => [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
        ],
    ]);
}

// ── Fallback ───────────────────────────────────────────────────────────────────

jsonResponse(['error' => "Unknown auth action: '{$action}'"], 404);
