<?php
/**
 * RanOnline Web Panel — Admin Operations Console & Kill Switch Manager
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/settings.php';
require_once __DIR__ . '/../includes/game_relay.php';

Auth::requireLogin();
$user = Auth::user();

// Verify Master Admin Authority (UserType >= 19)
if ((int)($user['user_type'] ?? 1) < 19) {
    http_response_code(403);
    die('
        <div style="background:#0a0d14;color:#f1f2f6;font-family:sans-serif;height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;">
            <div>
                <h1 style="color:#ff4757;font-size:36px;margin-bottom:12px;">403 Forbidden</h1>
                <p style="color:#8b9bb4;font-size:16px;">Access Denied. You do not have GM/DevOps administrative authority.</p>
                <p style="margin-top:20px;"><a href="../index.php" style="color:#00b4d8;">Return to Website</a></p>
            </div>
        </div>
    ');
}

$pdo = DB::connect();
$msgSuccess = '';
$msgError   = '';

// ── Handle Actions ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $msgError = 'Invalid security CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';

        // 1. Toggle Kill Switch (Instant On/Off)
        if ($action === 'toggle_killswitch') {
            $currentState = Settings::get('maintenance_mode', '0');
            $newState = ($currentState === '1') ? '0' : '1';
            Settings::set('maintenance_mode', $newState);
            
            $msgSuccess = ($newState === '1')
                ? 'OPS KILL SWITCH ENGAGED! Front page and public pages are now showing Server Maintenance.'
                : 'OPS Kill Switch Deactivated. Public site is now back live!';
        }

        // 2. Update Maintenance Information
        elseif ($action === 'update_maintenance_info') {
            $title   = trim($_POST['maintenance_title'] ?? '');
            $message = trim($_POST['maintenance_message'] ?? '');
            $eta     = trim($_POST['maintenance_eta'] ?? '');
            $discord = trim($_POST['maintenance_discord'] ?? '');

            Settings::set('maintenance_title', $title);
            Settings::set('maintenance_message', $message);
            Settings::set('maintenance_eta', $eta);
            Settings::set('maintenance_discord', $discord);

            $msgSuccess = 'Maintenance information updated successfully.';
        }

        // 3. Generate New Top-Up PIN Voucher
        elseif ($action === 'create_voucher') {
            $code   = strtoupper(trim($_POST['voucher_code'] ?? ''));
            $points = (int)($_POST['voucher_points'] ?? 0);

            if (empty($code)) {
                $code = 'RAN-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $points . 'P';
            }

            if ($points <= 0) {
                $msgError = 'Voucher points must be greater than 0.';
            } else {
                try {
                    $ins = $pdo->prepare("INSERT INTO web_pin_codes (pin_code, points, status) VALUES (:c, :p, 'active')");
                    $ins->execute([':c' => $code, ':p' => $points]);
                    $msgSuccess = "Voucher created: $code for $points Points!";
                } catch (Exception $e) {
                    $msgError = 'Voucher code already exists. Please choose another code.';
                }
            }
        }

        // 4. Publish News Announcement
        elseif ($action === 'publish_news') {
            $title    = trim($_POST['news_title'] ?? '');
            $category = trim($_POST['news_category'] ?? 'Announcement');
            $content  = trim($_POST['news_content'] ?? '');

            if (empty($title) || empty($content)) {
                $msgError = 'Title and content cannot be empty.';
            } else {
                $ins = $pdo->prepare("INSERT INTO web_news (title, category, content, author, created_at) VALUES (:t, :cat, :c, :a, NOW())");
                $ins->execute([':t' => $title, ':cat' => $category, ':c' => $content, ':a' => $user['username']]);
                $msgSuccess = 'News announcement published to the main page.';
            }
        }
    }
}

// Current Settings
$isMaintenanceActive = Settings::isMaintenanceActive();
$maintTitle   = Settings::get('maintenance_title', 'OPS SERVER MAINTENANCE IN PROGRESS');
$maintMessage = Settings::get('maintenance_message', 'Scheduled server updates and security upgrades are currently underway.');
$maintEta     = Settings::get('maintenance_eta', 'Estimated completion: within 1 to 2 hours');
$maintDiscord = Settings::get('maintenance_discord', DISCORD_URL);

// Metrics
$totalAccounts = (int)$pdo->query("SELECT COUNT(*) FROM web_accounts")->fetchColumn();
$totalOrders   = (int)$pdo->query("SELECT COUNT(*) FROM web_topup_orders WHERE status = 'completed'")->fetchColumn();
$totalRevenue  = (float)$pdo->query("SELECT COALESCE(SUM(amount), 0) FROM web_topup_orders WHERE status = 'completed'")->fetchColumn();

// Recent Top-Up Orders
$recentOrders = $pdo->query("SELECT * FROM web_topup_orders ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Active Vouchers
$activeVouchers = $pdo->query("SELECT * FROM web_pin_codes WHERE status = 'active' ORDER BY id DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Operations Console — <?= htmlspecialchars(APP_NAME) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Theme -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .admin-nav {
            background: #0d121d;
            border-bottom: 1px solid rgba(255, 71, 87, 0.3);
            padding: 12px 0;
        }
        .killswitch-card {
            border: 2px solid <?= $isMaintenanceActive ? '#ff4757' : 'rgba(46, 213, 115, 0.4)' ?>;
            background: <?= $isMaintenanceActive ? 'rgba(255, 71, 87, 0.08)' : 'rgba(46, 213, 115, 0.05)' ?>;
            box-shadow: 0 0 30px <?= $isMaintenanceActive ? 'rgba(255, 71, 87, 0.2)' : 'rgba(46, 213, 115, 0.1)' ?>;
        }
    </style>
</head>
<body>

    <!-- Admin Nav -->
    <div class="admin-nav">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="background: #ff4757; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 4px; text-transform: uppercase;">
                    <i class="fa-solid fa-lock"></i> Master Admin
                </span>
                <span style="font-size: 16px; font-weight: 700; color: #fff;">
                    <?= htmlspecialchars(APP_NAME) ?> Console
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <a href="../index.php" class="btn btn-outline" style="font-size: 12px; padding: 6px 14px;">
                    <i class="fa-solid fa-globe"></i> View Main Site
                </a>
                <a href="../logout.php" class="btn btn-outline" style="font-size: 12px; padding: 6px 12px; color: var(--danger);">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Admin Container -->
    <div class="container" style="margin: 30px auto 60px;">

        <?php if (!empty($msgSuccess)): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($msgSuccess) ?></div>
        <?php endif; ?>
        <?php if (!empty($msgError)): ?>
            <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($msgError) ?></div>
        <?php endif; ?>

        <!-- ── SECTION 1: EMERGENCY KILL SWITCH ────────────────────── -->
        <div class="card killswitch-card" style="margin-bottom: 30px; padding: 28px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 24px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                        <i class="fa-solid fa-power-off" style="font-size: 28px; color: <?= $isMaintenanceActive ? 'var(--danger)' : 'var(--success)' ?>;"></i>
                        <h2 style="font-size: 24px; margin-bottom: 0;">Emergency Kill Switch / Maintenance Mode</h2>
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px;">
                        Toggling this switch immediately redirects all public visitors from the home page to the custom Maintenance Splash screen.
                    </p>
                </div>

                <!-- Instant Toggle Button -->
                <form method="POST" action="index.php">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="toggle_killswitch">
                    <?php if ($isMaintenanceActive): ?>
                        <button type="submit" class="btn btn-primary" style="background: #2ed573; border-color: #2ed573; font-size: 15px; padding: 12px 28px;">
                            <i class="fa-solid fa-play"></i> DEACTIVATE KILL SWITCH (Go Live)
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-accent" style="font-size: 15px; padding: 12px 28px;" onclick="return confirm('Engage emergency kill switch? All public traffic will be directed to maintenance page.');">
                            <i class="fa-solid fa-hand"></i> ENGAGE KILL SWITCH (Maintenance)
                        </button>
                    <?php endif; ?>
                </form>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
                <span class="status-badge" style="font-size: 14px;">
                    <span class="status-dot <?= $isMaintenanceActive ? 'offline' : '' ?>"></span>
                    Current State: <strong><?= $isMaintenanceActive ? 'MAINTENANCE MODE IS ON (FRONT PAGE BLOCKED)' : 'SYSTEMS NORMAL (FRONT PAGE LIVE)' ?></strong>
                </span>
                <?php if ($isMaintenanceActive): ?>
                    <a href="../maintenance.php" target="_blank" style="font-size: 12px; text-decoration: underline; margin-left: 10px;">
                        Preview Maintenance Page &rarr;
                    </a>
                <?php endif; ?>
            </div>

            <!-- Maintenance Info Form -->
            <form method="POST" action="index.php" style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
                <?= Security::csrfField() ?>
                <input type="hidden" name="action" value="update_maintenance_info">

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" for="maintenance_title">Maintenance Splash Title</label>
                        <input type="text" id="maintenance_title" name="maintenance_title" class="form-control"
                               value="<?= htmlspecialchars($maintTitle) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="maintenance_eta">Estimated Completion Text</label>
                        <input type="text" id="maintenance_eta" name="maintenance_eta" class="form-control"
                               value="<?= htmlspecialchars($maintEta) ?>" placeholder="e.g. ETA: 30 minutes">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="maintenance_message">Maintenance Announcement Message (Displayed on front page)</label>
                    <textarea id="maintenance_message" name="maintenance_message" class="form-control" rows="3" required><?= htmlspecialchars($maintMessage) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="maintenance_discord">Discord Status Link</label>
                    <input type="url" id="maintenance_discord" name="maintenance_discord" class="form-control"
                           value="<?= htmlspecialchars($maintDiscord) ?>">
                </div>

                <button type="submit" class="btn btn-outline" style="font-size: 13px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Maintenance Texts
                </button>
            </form>
        </div>

        <!-- ── SECTION 2: METRICS OVERVIEW ────────────────────────── -->
        <div class="grid-3" style="margin-bottom: 30px;">
            <div class="card">
                <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Total Players</span>
                <div style="font-size: 32px; font-weight: 900; color: var(--primary); margin: 8px 0;">
                    <?= number_format($totalAccounts) ?>
                </div>
                <span style="font-size: 13px; color: var(--text-muted);">Registered web accounts</span>
            </div>

            <div class="card">
                <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Completed Top-Ups</span>
                <div style="font-size: 32px; font-weight: 900; color: var(--success); margin: 8px 0;">
                    <?= number_format($totalOrders) ?> Orders
                </div>
                <span style="font-size: 13px; color: var(--text-muted);">Transactions credited to game</span>
            </div>

            <div class="card">
                <span style="font-size: 12px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Gross Revenue</span>
                <div style="font-size: 32px; font-weight: 900; color: var(--accent-gold); margin: 8px 0;">
                    $<?= number_format($totalRevenue, 2) ?>
                </div>
                <span style="font-size: 13px; color: var(--text-muted);">Processed via web payments</span>
            </div>
        </div>

        <!-- ── SECTION 3: MANAGEMENT MODULES ──────────────────────── -->
        <div class="grid-2" style="margin-bottom: 30px;">
            <!-- Prepaid PIN Voucher Generator -->
            <div class="card">
                <h3 style="font-size: 18px; margin-bottom: 16px;">
                    <i class="fa-solid fa-ticket" style="color: var(--accent-gold);"></i> Create Prepaid Top-Up Voucher
                </h3>
                <form method="POST" action="index.php">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="create_voucher">

                    <div class="form-group">
                        <label class="form-label" for="voucher_code">PIN Code (Leave blank for auto-generate)</label>
                        <input type="text" id="voucher_code" name="voucher_code" class="form-control" placeholder="e.g. RAN-EVENT-500P">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="voucher_points">Points Value</label>
                        <input type="number" id="voucher_points" name="voucher_points" class="form-control" value="500" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-plus"></i> Generate Voucher
                    </button>
                </form>

                <div style="margin-top: 20px;">
                    <h4 style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">Active Redeemable Vouchers:</h4>
                    <?php if (!empty($activeVouchers)): ?>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($activeVouchers as $v): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; font-size: 13px;">
                                    <code style="color: var(--primary); font-weight: 700;"><?= htmlspecialchars($v['pin_code']) ?></code>
                                    <span style="color: var(--accent-gold); font-weight: 700;">+<?= number_format($v['points']) ?> P</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 12px; color: var(--text-muted);">No active vouchers.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- News Publisher -->
            <div class="card">
                <h3 style="font-size: 18px; margin-bottom: 16px;">
                    <i class="fa-solid fa-bullhorn" style="color: var(--primary);"></i> Post Front Page News
                </h3>
                <form method="POST" action="index.php">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="publish_news">

                    <div class="form-group">
                        <label class="form-label" for="news_title">Headline / Title</label>
                        <input type="text" id="news_title" name="news_title" class="form-control" required placeholder="e.g. Patch v1.0.2 Released">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="news_category">Category</label>
                        <select id="news_category" name="news_category" class="form-control">
                            <option value="Announcement">Announcement</option>
                            <option value="Event">Event</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Update">Update</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="news_content">Body Content</label>
                        <textarea id="news_content" name="news_content" class="form-control" rows="4" required placeholder="Write details here..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-outline btn-block">
                        <i class="fa-solid fa-paper-plane"></i> Publish Announcement
                    </button>
                </form>
            </div>
        </div>

        <!-- ── SECTION 4: RECENT TOP-UP ORDERS ────────────────────── -->
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-receipt" style="color: var(--primary);"></i> Recent Point Recharge Audit
            </h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Gateway</th>
                            <th>Amount</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Relay Sync</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($ro['order_id']) ?></code></td>
                                    <td><strong><?= htmlspecialchars($ro['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($ro['gateway']) ?></td>
                                    <td>$<?= number_format($ro['amount'], 2) ?></td>
                                    <td style="color: var(--accent-gold); font-weight: 700;">+<?= number_format($ro['points']) ?> P</td>
                                    <td>
                                        <span style="color: <?= ($ro['status'] === 'completed') ? 'var(--success)' : 'var(--accent-gold)' ?>; font-weight: 700; font-size: 12px;">
                                            <?= strtoupper(htmlspecialchars($ro['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= !empty($ro['game_credited']) 
                                            ? '<span style="color: var(--success);"><i class="fa-solid fa-check"></i> Credited</span>' 
                                            : '<span style="color: var(--text-muted);"><i class="fa-solid fa-clock"></i> Pending</span>' ?>
                                    </td>
                                    <td><?= date('M d, H:i', strtotime($ro['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">No top-up records yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Admin Footer -->
    <div style="text-align: center; color: var(--text-muted); font-size: 12px; margin-bottom: 30px;">
        &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?> DevOps Administration.
    </div>

</body>
</html>
