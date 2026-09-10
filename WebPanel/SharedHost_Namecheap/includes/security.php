<?php
/**
 * RanOnline Web Panel — Security & Protection Library
 * Handles CSRF, Rate Limiting, Input Sanitization, and Headers.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Security {

    /**
     * Get sanitized client IP address
     */
    public static function getClientIP(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare support
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($parts[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    /**
     * Generate or retrieve CSRF token
     */
    public static function getCsrfToken(): string {
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time'] > 3600)) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Render hidden CSRF form input
     */
    public static function csrfField(): string {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate submitted CSRF token
     */
    public static function validateCsrf(?string $submittedToken): bool {
        if (empty($submittedToken) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $submittedToken);
    }

    /**
     * Sanitize string input
     */
    public static function cleanString(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize username (letters, numbers, underscore only, 4-20 chars)
     */
    public static function sanitizeUsername(string $username): string {
        return preg_replace('/[^a-zA-Z0-9_]/', '', trim($username));
    }

    /**
     * Enforce rate limiting per IP and action key
     * Returns true if allowed, false if limit exceeded
     */
    public static function checkRateLimit(string $actionKey, int $maxAttempts, int $windowSeconds): bool {
        $ip = self::getClientIP();
        $now = time();

        try {
            $pdo = DB::connect();
            $stmt = $pdo->prepare("SELECT hits, first_attempt FROM web_rate_limits WHERE ip_address = :ip AND action_key = :action");
            $stmt->execute([':ip' => $ip, ':action' => $actionKey]);
            $record = $stmt->fetch();

            if (!$record) {
                $ins = $pdo->prepare("INSERT INTO web_rate_limits (ip_address, action_key, hits, first_attempt) VALUES (:ip, :action, 1, :now)");
                $ins->execute([':ip' => $ip, ':action' => $actionKey, ':now' => $now]);
                return true;
            }

            if ($now - $record['first_attempt'] > $windowSeconds) {
                // Window expired, reset counter
                $upd = $pdo->prepare("UPDATE web_rate_limits SET hits = 1, first_attempt = :now WHERE ip_address = :ip AND action_key = :action");
                $upd->execute([':now' => $now, ':ip' => $ip, ':action' => $actionKey]);
                return true;
            }

            if ($record['hits'] >= $maxAttempts) {
                return false; // Exceeded limit
            }

            // Increment count
            $upd = $pdo->prepare("UPDATE web_rate_limits SET hits = hits + 1 WHERE ip_address = :ip AND action_key = :action");
            $upd->execute([':ip' => $ip, ':action' => $actionKey]);
            return true;

        } catch (Exception $e) {
            // If DB error, fail gracefully without blocking user completely
            error_log("Rate limiting error: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Send standard defensive HTTP headers
     */
    public static function sendSecurityHeaders(): void {
        if (!headers_sent()) {
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: SAMEORIGIN");
            header("X-XSS-Protection: 1; mode=block");
            header("Referrer-Policy: strict-origin-when-cross-origin");
            header("Content-Security-Policy: default-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data: https:;");
        }
    }
}
