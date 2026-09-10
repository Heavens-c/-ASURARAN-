<?php
/**
 * RanOnline Web Panel — Server Settings & Kill Switch Engine
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

class Settings {
    private static ?array $cachedSettings = null;

    /**
     * Load all settings from MySQL
     */
    public static function loadAll(): array {
        if (self::$cachedSettings === null) {
            try {
                $pdo = DB::connect();
                $stmt = $pdo->query("SELECT setting_key, setting_value FROM web_settings");
                $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                self::$cachedSettings = $rows ?: [];
            } catch (Exception $e) {
                error_log("Failed to load web_settings: " . $e->getMessage());
                self::$cachedSettings = [];
            }
        }
        return self::$cachedSettings;
    }

    /**
     * Get a setting by key
     */
    public static function get(string $key, $default = ''): string {
        $settings = self::loadAll();
        return $settings[$key] ?? $default;
    }

    /**
     * Update or insert a setting
     */
    public static function set(string $key, string $value): bool {
        try {
            $pdo = DB::connect();
            $stmt = $pdo->prepare("REPLACE INTO web_settings (setting_key, setting_value) VALUES (:k, :v)");
            $res = $stmt->execute([':k' => $key, ':v' => $value]);
            if (self::$cachedSettings !== null) {
                self::$cachedSettings[$key] = $value;
            }
            return $res;
        } catch (Exception $e) {
            error_log("Failed to update setting {$key}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if Kill Switch / Maintenance Mode is enabled
     */
    public static function isMaintenanceActive(): bool {
        return (self::get('maintenance_mode', '0') === '1');
    }

    /**
     * Guard middleware to enforce maintenance mode
     * Allows Administrators (user_type >= 19) to bypass and manage the server.
     */
    public static function enforceMaintenanceGuard(): void {
        if (!self::isMaintenanceActive()) {
            return;
        }

        $user = Auth::user();
        // Allow Admins to bypass maintenance
        if ($user && (int)($user['user_type'] ?? 1) >= 19) {
            return;
        }

        $currentScript = basename($_SERVER['PHP_SELF'] ?? '');
        $currentUri    = $_SERVER['REQUEST_URI'] ?? '';

        // Allow access to login (so GM/Admin can sign in) and admin panel
        if ($currentScript === 'login.php' || strpos($currentUri, '/admin/') !== false || $currentScript === 'maintenance.php') {
            return;
        }

        // Redirect public visitors to maintenance page
        header('Location: maintenance.php');
        exit;
    }
}
