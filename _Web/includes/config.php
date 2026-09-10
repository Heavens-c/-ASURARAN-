<?php
/**
 * RanOnline Web Panel — Configuration
 * SECURITY: This file must NEVER be accessible from the web.
 * Place it ABOVE public_html or protect with .htaccess.
 */

// ── Environment ──────────────────────────────────────────────
define('APP_NAME',    'RanOnline');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'production');  // 'development' or 'production'
define('APP_DEBUG',   false);        // NEVER true in production
define('APP_URL',     'https://yourdomain.com');  // Change to your domain
define('APP_TIMEZONE','Asia/Manila');

date_default_timezone_set(APP_TIMEZONE);

// ── MySQL Database (Namecheap Shared Hosting) ────────────────
define('DB_HOST',     'localhost');
define('DB_NAME',     'your_cpanel_dbname');  // e.g. cpuser_ranonline
define('DB_USER',     'your_cpanel_dbuser');  // e.g. cpuser_ranuser
define('DB_PASS',     'your_db_password');
define('DB_CHARSET',  'utf8mb4');

// ── Security Keys (CHANGE THESE — use random 64-char strings) ─
define('CSRF_SECRET',    'CHANGE_ME_64_CHAR_RANDOM_STRING_1_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('SESSION_SECRET', 'CHANGE_ME_64_CHAR_RANDOM_STRING_2_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('API_TOKEN',      'CHANGE_ME_64_CHAR_RANDOM_STRING_3_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// ── Game Server API Relay ────────────────────────────────────
// The ONLY connection to the game server — for point insertion
define('GAME_API_URL',       'https://YOUR_GAME_SERVER_IP:8443/api');
define('GAME_API_TOKEN',     API_TOKEN);
define('GAME_SERVER_IP',     '127.0.0.1');  // Whitelist: game server IP

// ── Payment (PayPal — change to your credentials) ────────────
define('PAYPAL_MODE',        'sandbox');  // 'sandbox' or 'live'
define('PAYPAL_CLIENT_ID',   'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET',      'YOUR_PAYPAL_SECRET');
define('PAYPAL_WEBHOOK_ID',  'YOUR_PAYPAL_WEBHOOK_ID');
define('PAYPAL_CURRENCY',    'USD');

// ── Email (for password resets) ──────────────────────────────
define('MAIL_FROM',     'noreply@yourdomain.com');
define('MAIL_FROM_NAME','RanOnline');
define('MAIL_METHOD',   'phpmail');  // 'phpmail' or 'smtp'
// SMTP settings (only if MAIL_METHOD = 'smtp')
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USER',     '');
define('SMTP_PASS',     '');

// ── Rate Limiting ────────────────────────────────────────────
define('RATE_LOGIN_MAX',     5);   // Max login attempts
define('RATE_LOGIN_WINDOW',  300); // Per 5 minutes
define('RATE_REGISTER_MAX',  3);
define('RATE_REGISTER_WINDOW', 3600);
define('RATE_RESET_MAX',     3);
define('RATE_RESET_WINDOW',  3600);

// ── Top-Up Packages ──────────────────────────────────────────
// Defined in DB but these are defaults for install
define('DEFAULT_PACKAGES', json_encode([
    ['name' => '100 Points',  'points' => 100,  'price' => 1.00],
    ['name' => '500 Points',  'points' => 500,  'price' => 4.00],
    ['name' => '1000 Points', 'points' => 1000, 'price' => 7.00],
    ['name' => '5000 Points', 'points' => 5000, 'price' => 30.00],
]));

// ── Session Configuration ────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure',  1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 3600);

// ── Error Handling ───────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}
