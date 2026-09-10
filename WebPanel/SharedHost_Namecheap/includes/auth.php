<?php
/**
 * RanOnline Web Panel — Authentication & Session Manager
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/game_relay.php';

class Auth {

    /**
     * Check if a visitor is currently logged in
     */
    public static function check(): bool {
        return !empty($_SESSION['web_user_id']) && !empty($_SESSION['web_username']);
    }

    /**
     * Retrieve current logged-in user array
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }

        return [
            'id'           => $_SESSION['web_user_id'],
            'username'     => $_SESSION['web_username'],
            'email'        => $_SESSION['web_user_email'] ?? '',
            'user_type'    => $_SESSION['web_user_type'] ?? 1,
            'game_usernum' => $_SESSION['web_game_usernum'] ?? null
        ];
    }

    /**
     * Require authentication for protected pages
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            $_SESSION['flash_error'] = 'Please log in to access this page.';
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Register a new player account (Creates web account + provisions on Windows Game Server via Relay)
     */
    public static function register(string $username, string $password, string $email, string $pincode = ''): array {
        $username = Security::sanitizeUsername($username);
        $email    = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

        if (!$username || strlen($username) < 4 || strlen($username) > 20) {
            return ['success' => false, 'error' => 'Username must be 4 to 20 alphanumeric characters.'];
        }

        if (!$email) {
            return ['success' => false, 'error' => 'Please enter a valid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters.'];
        }

        $pdo = DB::connect();

        // Check if username or email is already taken locally
        $stmt = $pdo->prepare("SELECT id FROM web_accounts WHERE username = :u OR email = :e LIMIT 1");
        $stmt->execute([':u' => $username, ':e' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'Username or Email is already registered.'];
        }

        // Provision account onto the Windows Game Server database via the safe Relay
        $relayRes = GameRelay::registerGameAccount($username, $password, $email, $pincode);
        $gameUserNum = null;

        if (!empty($relayRes['success']) && !empty($relayRes['usernum'])) {
            $gameUserNum = (int)$relayRes['usernum'];
        }

        // Store local web account
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $ins = $pdo->prepare("
            INSERT INTO web_accounts (username, password_hash, email, pincode, game_usernum, user_type, created_at)
            VALUES (:u, :p, :e, :pin, :gn, 1, NOW())
        ");
        $ins->execute([
            ':u'   => $username,
            ':p'   => $passwordHash,
            ':e'   => $email,
            ':pin' => $pincode,
            ':gn'  => $gameUserNum
        ]);

        return [
            'success'     => true,
            'message'     => 'Registration successful! You may now log in to the website and game.',
            'relay_sync'  => $relayRes['success'] ?? false
        ];
    }

    /**
     * Authenticate player login
     */
    public static function login(string $username, string $password): array {
        $username = Security::sanitizeUsername($username);
        $pdo = DB::connect();

        $stmt = $pdo->prepare("SELECT * FROM web_accounts WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        if (!empty($user['is_blocked'])) {
            return ['success' => false, 'error' => 'Your account has been suspended. Please contact GM support.'];
        }

        // Prevent session fixation
        session_regenerate_id(true);

        $_SESSION['web_user_id']      = $user['id'];
        $_SESSION['web_username']     = $user['username'];
        $_SESSION['web_user_email']    = $user['email'];
        $_SESSION['web_user_type']     = $user['user_type'];
        $_SESSION['web_game_usernum']  = $user['game_usernum'];

        // Update login metadata
        $upd = $pdo->prepare("UPDATE web_accounts SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id");
        $upd->execute([':ip' => Security::getClientIP(), ':id' => $user['id']]);

        return ['success' => true];
    }

    /**
     * Terminate user session
     */
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }
}
