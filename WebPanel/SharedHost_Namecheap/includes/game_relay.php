<?php
/**
 * RanOnline Web Panel — Game Server Relay Client
 * 
 * SECURITY ARCHITECTURE:
 * This module is the ONLY outbound link from the Namecheap Shared Hosting environment
 * to the Windows Game Server.
 * 
 * - PostgreSQL is NEVER exposed to the public internet.
 * - All communication is cryptographically signed with HMAC-SHA256.
 * - Timestamps and Nonces prevent replay attacks.
 * - Transactions are idempotent: points are safely credited with order ID deduplication.
 */

require_once __DIR__ . '/config.php';

class GameRelay {

    /**
     * Send signed HMAC-SHA256 request to the Windows Game Server Relay API
     */
    public static function send(string $action, array $data = []): array {
        $url = rtrim(GAME_RELAY_URL, '/') . '/' . ltrim($action, '/');
        
        $payload = [
            'action'    => $action,
            'timestamp' => time(),
            'nonce'     => bin2hex(random_bytes(8)),
            'data'      => $data
        ];

        $jsonPayload = json_encode($payload);
        $signature   = hash_hmac('sha256', $jsonPayload, GAME_RELAY_SECRET);

        $headers = [
            'Content-Type: application/json',
            'X-Signature: ' . $signature,
            'X-Timestamp: ' . $payload['timestamp'],
            'User-Agent: RanOnline-Web-Relay/1.0'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, GAME_RELAY_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local self-signed certs if used
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("GameRelay curl error connecting to $url: " . $curlError);
            return [
                'success' => false,
                'error'   => 'Game server relay connection error: ' . $curlError,
                'offline' => true
            ];
        }

        $decoded = json_decode($response, true);
        if ($httpCode !== 200 || !is_array($decoded)) {
            error_log("GameRelay bad response ($httpCode): " . $response);
            return [
                'success' => false,
                'error'   => $decoded['error'] ?? 'Game server returned invalid response (' . $httpCode . ')'
            ];
        }

        return $decoded;
    }

    /**
     * Primary action: Credit points to a player on the game server
     * 
     * @param string $username  RanOnline UserID
     * @param int    $points    Points amount to credit
     * @param string $orderId   Unique transaction reference
     * @return array
     */
    public static function insertPoints(string $username, int $points, string $orderId): array {
        return self::send('insert_points', [
            'username' => $username,
            'points'   => $points,
            'order_id' => $orderId
        ]);
    }

    /**
     * Verify if an account exists on the game server
     */
    public static function checkUser(string $username): array {
        return self::send('check_user', [
            'username' => $username
        ]);
    }

    /**
     * Register account into the game server's UserInfo table
     */
    public static function registerGameAccount(string $username, string $password, string $email, string $pincode = ''): array {
        return self::send('register_user', [
            'username' => $username,
            'password' => $password, // Relay will hash/store matching Ran's UserPass format
            'email'    => $email,
            'pincode'  => $pincode
        ]);
    }

    /**
     * Check if game server processes are online (ServerLogin, ServerField, etc.)
     */
    public static function getServerStatus(): array {
        return self::send('get_server_status');
    }

    /**
     * Fetch live character & guild rankings from the game database
     */
    public static function getRankings(int $limit = 50): array {
        return self::send('get_rankings', [
            'limit' => $limit
        ]);
    }
}
