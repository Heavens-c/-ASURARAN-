<?php
/**
 * RanOnline Web Panel — Player Dashboard & Account Control Panel
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Player Dashboard — ' . APP_NAME;
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game_relay.php';
require_once __DIR__ . '/includes/constants.php';

Auth::requireLogin();
$user = Auth::user();
$pdo = DB::connect();

// Fetch local web account info
$stmt = $pdo->prepare("SELECT * FROM web_accounts WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user['id']]);
$accountInfo = $stmt->fetch();

// Fetch live game account data via safe Relay
$gameAccount = GameRelay::checkUser($user['username']);
$gamePoints = $gameAccount['user']['userpoint'] ?? 0;
$userCharacters = $gameAccount['characters'] ?? [];

// Fetch user's top-up orders
$orderStmt = $pdo->prepare("SELECT * FROM web_topup_orders WHERE username = :u ORDER BY created_at DESC LIMIT 10");
$orderStmt->execute([':u' => $user['username']]);
$recentOrders = $orderStmt->fetchAll();

// Handle Password Change
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_pass') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPass, $accountInfo['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif ($newPass !== $confirmPass) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPass) < 6) {
            $error = 'New password must be at least 6 characters.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT);
            $uStmt = $pdo->prepare("UPDATE web_accounts SET password_hash = :p WHERE id = :id");
            $uStmt->execute([':p' => $newHash, ':id' => $user['id']]);
            $success = 'Password has been updated successfully.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin: 30px auto 60px;">
    <!-- Welcome Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px;">Welcome, <?= htmlspecialchars($user['username']) ?></h1>
            <p style="color: var(--text-muted); font-size: 14px;">Member since <?= date('F d, Y', strtotime($accountInfo['created_at'])) ?></p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="topup.php" class="btn btn-primary">
                <i class="fa-solid fa-coins"></i> Recharge Points
            </a>
            <a href="shop.php" class="btn btn-outline">
                <i class="fa-solid fa-store"></i> Item Mall
            </a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Summary Widgets -->
    <div class="grid-3" style="margin-bottom: 30px;">
        <div class="card">
            <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Game Points</span>
            <div style="font-size: 36px; font-weight: 900; color: var(--accent-gold); margin: 8px 0;">
                <i class="fa-solid fa-coins"></i> <?= number_format($gamePoints) ?>
            </div>
            <span style="font-size: 13px; color: var(--text-muted);">Synced in real-time from Game Server</span>
        </div>

        <div class="card">
            <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Account Authority</span>
            <div style="font-size: 28px; font-weight: 800; color: var(--primary); margin: 12px 0;">
                <?= ((int)$accountInfo['user_type'] >= 19) ? 'Administrator' : 'Standard Player' ?>
            </div>
            <span style="font-size: 13px; color: var(--text-muted);">Level Type: <?= (int)$accountInfo['user_type'] ?></span>
        </div>

        <div class="card">
            <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Security PIN</span>
            <div style="font-size: 28px; font-weight: 800; color: var(--success); margin: 12px 0;">
                <?= !empty($accountInfo['pincode']) ? 'PROTECTED' : 'NOT SET' ?>
            </div>
            <span style="font-size: 13px; color: var(--text-muted);">Protects in-game character deletion</span>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid-2" style="margin-bottom: 30px;">
        <!-- Character List -->
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-users" style="color: var(--primary);"></i> In-Game Characters
            </h3>
            
            <?php if (!empty($userCharacters)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>School</th>
                                <th>Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userCharacters as $cha): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($cha['chaname'] ?? 'Unknown') ?></strong></td>
                                    <td><?= htmlspecialchars(getClassName((int)($cha['chaclass'] ?? 0))) ?></td>
                                    <td>
                                        <span style="color: <?= getSchoolColor((int)($cha['chaschool'] ?? 0)) ?>; font-weight: 600;">
                                            <?= htmlspecialchars(getSchoolName((int)($cha['chaschool'] ?? 0))) ?>
                                        </span>
                                    </td>
                                    <td>Lv. <?= (int)($cha['chalevel'] ?? 1) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 13px;">No characters created yet. Launch the game client to create one!</p>
            <?php endif; ?>
        </div>

        <!-- Security / Password Change -->
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-key" style="color: var(--accent-gold);"></i> Change Password
            </h3>
            
            <form method="POST" action="dashboard.php">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="change_pass">

                <div class="form-group">
                    <label class="form-label" for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button type="submit" class="btn btn-outline btn-block">
                    <i class="fa-solid fa-save"></i> Save New Password
                </button>
            </form>
        </div>
    </div>

    <!-- Top-up Transaction History -->
    <div class="card">
        <h3 style="font-size: 18px; margin-bottom: 16px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary);"></i> Recent Point Recharges
        </h3>

        <?php if (!empty($recentOrders)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Game Credit</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($order['order_id']) ?></code></td>
                                <td><?= htmlspecialchars($order['package_name']) ?></td>
                                <td>$<?= number_format($order['amount'], 2) ?> <?= htmlspecialchars($order['currency']) ?></td>
                                <td style="color: var(--accent-gold); font-weight: 700;">+<?= number_format($order['points']) ?> P</td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 700; color: <?= ($order['status'] === 'completed') ? 'var(--success)' : 'var(--accent-gold)' ?>;">
                                        <?= strtoupper(htmlspecialchars($order['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= !empty($order['game_credited']) 
                                        ? '<span style="color: var(--success);"><i class="fa-solid fa-check"></i> Credited</span>' 
                                        : '<span style="color: var(--text-muted);"><i class="fa-solid fa-hourglass"></i> Pending</span>' ?>
                                </td>
                                <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 13px;">No transactions found. Ready to power up your hero? Visit the <a href="topup.php">Top-Up</a> page.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
