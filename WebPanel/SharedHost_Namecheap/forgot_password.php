<?php
/**
 * RanOnline Web Panel — Forgot Password
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Forgot Password — ' . APP_NAME;
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/db.php';

$error = '';
$success = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif (!Security::checkRateLimit('forgot_pass', 3, 3600)) {
        $error = 'Too many password reset requests. Please wait an hour.';
    } else {
        $input = trim($_POST['username_or_email'] ?? '');
        $pdo = DB::connect();

        $stmt = $pdo->prepare("SELECT id, username, email FROM web_accounts WHERE username = :u OR email = :e LIMIT 1");
        $stmt->execute([':u' => $input, ':e' => $input]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $ins = $pdo->prepare("INSERT INTO web_password_resets (username, token, expires_at) VALUES (:u, :t, :exp)");
            $ins->execute([':u' => $user['username'], ':t' => $token, ':exp' => $expiresAt]);

            $resetUrl = rtrim(APP_URL, '/') . '/forgot_password.php?token=' . $token;
            
            // For testing & local convenience if SMTP isn't set up yet:
            $success = 'Password reset instructions have been generated. Follow the link below:';
            $resetLink = $resetUrl;
        } else {
            // Ambiguous message for security
            $success = 'If the account exists, password recovery instructions have been initiated.';
        }
    }
}

// Handling password reset submission if token is provided
$tokenParam = $_GET['token'] ?? '';
$validTokenUser = null;
if (!empty($tokenParam)) {
    $pdo = DB::connect();
    $tstmt = $pdo->prepare("SELECT * FROM web_password_resets WHERE token = :t AND used = 0 AND expires_at > NOW() LIMIT 1");
    $tstmt->execute([':t' => $tokenParam]);
    $validTokenUser = $tstmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_password') {
    $submittedToken = $_POST['token'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confPass = $_POST['confirm_password'] ?? '';

    if ($newPass !== $confPass) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $pdo = DB::connect();
        $tstmt = $pdo->prepare("SELECT * FROM web_password_resets WHERE token = :t AND used = 0 AND expires_at > NOW() LIMIT 1");
        $tstmt->execute([':t' => $submittedToken]);
        $resetRecord = $tstmt->fetch();

        if ($resetRecord) {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            
            // Update web password
            $uupd = $pdo->prepare("UPDATE web_accounts SET password_hash = :p WHERE username = :u");
            $uupd->execute([':p' => $newHash, ':u' => $resetRecord['username']]);

            // Mark token used
            $tupd = $pdo->prepare("UPDATE web_password_resets SET used = 1 WHERE id = :id");
            $tupd->execute([':id' => $resetRecord['id']]);

            $_SESSION['flash_success'] = 'Password has been reset successfully! You can now log in.';
            header('Location: login.php');
            exit;
        } else {
            $error = 'Reset link has expired or is invalid.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 480px; margin: 60px auto 80px;">
    <div class="card">
        <?php if ($validTokenUser): ?>
            <div style="text-align: center; margin-bottom: 24px;">
                <i class="fa-solid fa-key" style="font-size: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h2 style="font-size: 24px;">Set New Password</h2>
                <p style="color: var(--text-muted); font-size: 13px;">Resetting password for: <strong><?= htmlspecialchars($validTokenUser['username']) ?></strong></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="forgot_password.php">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="new_password">
                <input type="hidden" name="token" value="<?= htmlspecialchars($tokenParam) ?>">

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 12px;">
                    <i class="fa-solid fa-check"></i> Update Password
                </button>
            </form>

        <?php else: ?>
            <div style="text-align: center; margin-bottom: 24px;">
                <i class="fa-solid fa-shield-heart" style="font-size: 32px; color: var(--primary); margin-bottom: 12px;"></i>
                <h2 style="font-size: 24px;">Recover Password</h2>
                <p style="color: var(--text-muted); font-size: 13px;">Enter your Username or Email to reset your password</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <div>
                        <?= htmlspecialchars($success) ?>
                        <?php if ($resetLink): ?>
                            <div style="margin-top: 10px;">
                                <a href="<?= htmlspecialchars($resetLink) ?>" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px;">
                                    Click here to set new password &rarr;
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot_password.php">
                <?= Security::csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="username_or_email">Username or Registered Email</label>
                    <input type="text" id="username_or_email" name="username_or_email" class="form-control" required placeholder="Enter username or email">
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 12px;">
                    <i class="fa-solid fa-paper-plane"></i> Send Recovery Request
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; font-size: 13px; color: var(--text-muted);">
                Remembered your password? <a href="login.php">Log In</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
