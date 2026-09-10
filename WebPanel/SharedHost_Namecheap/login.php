<?php
/**
 * RanOnline Web Panel — Account Login
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Login — ' . APP_NAME;
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security session. Please try again.';
    } elseif (!Security::checkRateLimit('login', RATE_LOGIN_MAX, RATE_LOGIN_WINDOW)) {
        $error = 'Too many failed login attempts. Please wait 5 minutes before trying again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $loginRes = Auth::login($username, $password);
        if ($loginRes['success']) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = $loginRes['error'] ?? 'Invalid credentials.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 440px; margin: 60px auto 80px;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fa-solid fa-lock" style="font-size: 32px; color: var(--primary); margin-bottom: 12px;"></i>
            <h2 style="font-size: 24px;">Member Sign In</h2>
            <p style="color: var(--text-muted); font-size: 13px;">Manage your account and game points</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="username">Username / Game ID</label>
                <input type="text" id="username" name="username" class="form-control" required
                       placeholder="Enter your username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label class="form-label" for="password" style="margin-bottom: 0;">Password</label>
                    <a href="forgot_password.php" style="font-size: 12px;">Forgot Password?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" required
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 15px; margin-top: 10px;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted);">
            Don't have an account yet? <a href="register.php" style="font-weight: 600;">Create One</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
