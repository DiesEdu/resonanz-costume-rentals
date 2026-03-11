<?php
// api/middleware/AuthMiddleware.php
namespace SheetMusic\Middleware;

require_once __DIR__ . '/../config/database.php';

// Firebase\JWT is preferred when installed; we fall back to a tiny HS256 verifier and legacy token verifier if not available.
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    public static function authenticate()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No authorization token provided']);
            exit();
        }

        $auth_header = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $auth_header);

        try {
            // Legacy HMAC token (base64(userId|timestamp|hmac)) used by auth.php
            if (strpos($token, '.') === false) {
                return self::decodeLegacyToken($token);
            }

            if (class_exists(JWT::class)) {
                $decoded = JWT::decode($token, new Key(JWT_SECRET_KEY, 'HS256'));
                return (array) $decoded->data;
            }

            // Fallback HS256 decode (minimal) when Firebase\JWT is unavailable
            $payload = self::decodeHs256($token, JWT_SECRET_KEY);
            return (array) ($payload['data'] ?? []);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit();
        }
    }

    public static function requireAdminOrManager()
    {
        $user = self::authenticate();

        if ($user['role'] !== 'admin' && $user['role'] !== 'costume_management') {
            http_response_code(403);
            echo json_encode(['error' => 'Admin/Manager access required']);
            exit();
        }

        return $user;
    }

    /**
     * Verify the legacy base64 token generated in api/auth.php (userId|timestamp|HMAC).
     */
    private static function decodeLegacyToken(string $token): array
    {
        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            throw new \Exception('Malformed token');
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            throw new \Exception('Malformed token');
        }

        [$userId, $issuedAt, $sig] = $parts;
        $userId = (int) $userId;
        $issuedAt = (int) $issuedAt;

        $secret = defined('APP_SECRET') ? APP_SECRET : 'costume_rental_secret_2024';
        $payload = $userId . '|' . $issuedAt;
        $expectedSig = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSig, $sig)) {
            throw new \Exception('Signature verification failed');
        }

        // Optional expiry: 30 days validity
        if ($issuedAt > 0 && time() - $issuedAt > 30 * 24 * 60 * 60) {
            throw new \Exception('Token expired');
        }

        // Fetch user role from DB
        $db = getDB();
        $stmt = $db->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        if (!$user) {
            throw new \Exception('User not found');
        }

        return [
            'id' => (int) $user['id'],
            'role' => $user['role'] ?? 'customer',
        ];
    }

    /**
     * Minimal HS256 JWT decoder (signature verify + optional exp check).
     * Throws \Exception on failure.
     */
    private static function decodeHs256(string $jwt, string $secret): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Malformed token');
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        $header = json_decode(self::b64UrlDecode($headerB64), true);
        $payload = json_decode(self::b64UrlDecode($payloadB64), true);
        if (!is_array($header) || !is_array($payload)) {
            throw new \Exception('Invalid token encoding');
        }

        $expected = self::b64UrlEncode(hash_hmac('sha256', "{$headerB64}.{$payloadB64}", $secret, true));
        if (!hash_equals($expected, $sigB64)) {
            throw new \Exception('Signature verification failed');
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }

    private static function b64UrlDecode(string $input): string
    {
        $replaced = strtr($input, '-_', '+/');
        $padded = str_pad($replaced, strlen($replaced) % 4 === 0 ? strlen($replaced) : strlen($replaced) + 4 - strlen($replaced) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($padded, true) ?: '';
    }

    private static function b64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
?>
