<?php
/**
 * RanOnline Web Panel — Account Registration
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Create Account — ' . APP_NAME;
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

if (Auth::check()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please refresh and try again.';
    }
    // 2. Enforce Rate Limit
    elseif (!Security::checkRateLimit('register', RATE_REGISTER_MAX, RATE_REGISTER_WINDOW)) {
        $error = 'Too many registration attempts from your IP. Please wait before trying again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $pincode  = trim($_POST['pincode'] ?? '');

        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!empty($pincode) && !preg_match('/^[0-9]{4,6}$/', $pincode)) {
            $error = 'PIN Code must be 4 to 6 numeric digits.';
        } else {
            $regRes = Auth::register($username, $password, $email, $pincode);
            if ($regRes['success']) {
                $_SESSION['flash_success'] = 'Account created successfully! You can now log in.';
                header('Location: login.php');
                exit;
            } else {
                $error = $regRes['error'] ?? 'Registration failed.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 520px; margin: 40px auto 60px;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 24px;">
            <i class="fa-solid fa-user-plus" style="font-size: 32px; color: var(--primary); margin-bottom: 12px;"></i>
            <h2 style="font-size: 24px;">Create an Account</h2>
            <p style="color: var(--text-muted); font-size: 13px;">Join RanOnline today and start your journey</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <?= Security::csrfField() ?>

            <div class="form-group">
                <label class="form-label" for="username">Game ID / Username</label>
                <input type="text" id="username" name="username" class="form-control" required
                       minlength="4" maxlength="20" placeholder="4-20 alphanumeric characters"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required
                       placeholder="valid.email@domain.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_password">Password</label>
                <input type="password" id="reg_password" name="password" class="form-control" required
                       minlength="6" placeholder="At least 6 characters">
            </div>

            <div class="form-group">
                <label class="form-label" for="reg_confirm_password">Confirm Password</label>
                <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control" required
                       placeholder="Repeat your password">
                <div id="pass_match_error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: none;"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="pincode">Security PIN Code (Optional)</label>
                <input type="text" id="pincode" name="pincode" class="form-control" maxlength="6"
                       placeholder="4 to 6 digits for in-game deletion/storage"
                       value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 15px; margin-top: 10px;">
                <i class="fa-solid fa-check"></i> Register Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="font-weight: 600;">Sign In</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
