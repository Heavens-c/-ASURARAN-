<?php
/**
 * RanOnline Web Panel — Central Configuration
 * Compatible with Namecheap Shared Hosting (cPanel) & PHP 8.x
 */

// Prevent direct execution
if (count(get_included_files()) === 1) {
    http_response_code(403);
    exit('Access Denied');
}

// ── Application Settings ───────────────────────────────────────
define('APP_NAME',         'RanOnline MMORPG');
define('APP_TAGLINE',      'Modern Classic Ran Server');
define('APP_URL',          'https://yourdomain.com'); // Your Namecheap website URL
define('APP_TIMEZONE',     'Asia/Manila');
define('APP_ENV',          'production');             // 'development' or 'production'
define('APP_DEBUG',        false);                    // Set to false in production

date_default_timezone_set(APP_TIMEZONE);

// ── Namecheap MySQL Database Configuration ─────────────────────
define('DB_HOST',          'localhost');
define('DB_NAME',          'cpaneluser_ranweb');      // cPanel MySQL database name
define('DB_USER',          'cpaneluser_webuser');     // cPanel MySQL user
define('DB_PASS',          'StrongPasswordHere123!'); // cPanel MySQL password
define('DB_CHARSET',       'utf8mb4');

// ── Windows Game Server Relay API (The ONLY bridge to Windows) ──
// The Windows Game Server runs the Relay daemon locally next to PostgreSQL.
// Namecheap sends HMAC-SHA256 authenticated API requests to this endpoint.
define('GAME_RELAY_URL',    'http://YOUR_WINDOWS_SERVER_IP:8088/api');
define('GAME_RELAY_SECRET', 'CHANGE_THIS_HMAC_SECRET_KEY_MINIMUM_64_CHARACTERS_1234567890abcdef');
define('GAME_RELAY_TIMEOUT', 5); // 5 seconds HTTP timeout

// ── Server Info (For Display / Downloads) ──────────────────────
define('GAME_SERVER_NAME', 'Genesis');
define('GAME_SERVER_RATES', 'Exp: x5 | Drop: x2 | Gold: x2 | Max Lv: 230 | Max Skill: 207');
define('CLIENT_DOWNLOAD_URL', 'https://mega.nz/file/your-client-archive');
define('DISCORD_URL',      'https://discord.gg/yourserver');
define('FACEBOOK_URL',     'https://facebook.com/yourpage');

// ── Security & Session Keys ───────────────────────────────────
define('CSRF_KEY',         'GENERATE_CSRF_KEY_SALT_STRING_RANDOM_48_CHARS_abcdefghijklmnop');
define('SESSION_LIFETIME', 7200); // 2 hours

// ── Rate Limiting Limits ──────────────────────────────────────
define('RATE_LOGIN_MAX',      5);    // 5 attempts
define('RATE_LOGIN_WINDOW',   300);  // 5 minutes window
define('RATE_REGISTER_MAX',   3);    // 3 registrations
define('RATE_REGISTER_WINDOW', 3600); // 1 hour window
define('RATE_TOPUP_MAX',      10);   // 10 topup tries
define('RATE_TOPUP_WINDOW',   600);  // 10 minutes

// ── Top-Up Packages ───────────────────────────────────────────
$GLOBALS['TOPUP_PACKAGES'] = [
    ['id' => 1, 'name' => 'Starter Pack',   'points' => 100,  'price' => 2.00,  'currency' => 'USD', 'bonus' => '0% Bonus'],
    ['id' => 2, 'name' => 'Combatant Pack', 'points' => 300,  'price' => 5.00,  'currency' => 'USD', 'bonus' => '+20 Bonus'],
    ['id' => 3, 'name' => 'Elite Pack',     'points' => 700,  'price' => 10.00, 'currency' => 'USD', 'bonus' => '+70 Bonus'],
    ['id' => 4, 'name' => 'Legendary Pack', 'points' => 1500, 'price' => 20.00, 'currency' => 'USD', 'bonus' => '+200 Bonus'],
    ['id' => 5, 'name' => 'Titan Pack',     'points' => 4000, 'price' => 50.00, 'currency' => 'USD', 'bonus' => '+600 Bonus'],
];

// ── Session Security Hardening ────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

// ── Error Reporting ───────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
