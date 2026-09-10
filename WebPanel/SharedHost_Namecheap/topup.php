<?php
/**
 * RanOnline Web Panel — Top-Up & Point Recharge Portal
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Top-Up Points — ' . APP_NAME;
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/game_relay.php';

Auth::requireLogin();
$user = Auth::user();
$pdo = DB::connect();

$error = '';
$success = '';

// ── Action 1: Redeem PIN Voucher ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'redeem_pin') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } elseif (!Security::checkRateLimit('redeem_pin', RATE_TOPUP_MAX, RATE_TOPUP_WINDOW)) {
        $error = 'Too many attempts. Please wait a few minutes before trying another PIN.';
    } else {
        $pin = strtoupper(trim($_POST['pin_code'] ?? ''));

        $stmt = $pdo->prepare("SELECT * FROM web_pin_codes WHERE pin_code = :pin AND status = 'active' LIMIT 1 FOR UPDATE");
        $pdo->beginTransaction();
        $stmt->execute([':pin' => $pin]);
        $voucher = $stmt->fetch();

        if (!$voucher) {
            $pdo->rollBack();
            $error = 'Invalid, expired, or already used PIN code.';
        } else {
            // Mark voucher used
            $upd = $pdo->prepare("UPDATE web_pin_codes SET status = 'used', used_by = :u, used_at = NOW() WHERE id = :id");
            $upd->execute([':u' => $user['username'], ':id' => $voucher['id']]);

            // Create record in web_topup_orders
            $orderId = 'PIN-' . strtoupper(bin2hex(random_bytes(6)));
            $insOrder = $pdo->prepare("
                INSERT INTO web_topup_orders (order_id, username, gateway, package_name, amount, currency, points, status, transaction_id, ip_address)
                VALUES (:oid, :u, 'pin_voucher', 'Voucher Redemption', 0.00, 'USD', :pts, 'completed', :tx, :ip)
            ");
            $insOrder->execute([
                ':oid' => $orderId,
                ':u'   => $user['username'],
                ':pts' => $voucher['points'],
                ':tx'  => $voucher['pin_code'],
                ':ip'  => Security::getClientIP()
            ]);

            $pdo->commit();

            // Insert points to Windows Game Server via safe Relay
            $relayRes = GameRelay::insertPoints($user['username'], (int)$voucher['points'], $orderId);

            if (!empty($relayRes['success'])) {
                // Update local order as game credited
                $uOrd = $pdo->prepare("UPDATE web_topup_orders SET game_credited = 1, credited_at = NOW() WHERE order_id = :oid");
                $uOrd->execute([':oid' => $orderId]);

                $success = sprintf('Success! %d Points have been credited directly to your in-game account!', $voucher['points']);
            } else {
                $error = 'PIN redeemed, but game server relay is temporarily offline. Your points will be credited on next server sync.';
            }
        }
    }
}

// ── Action 2: Buy Top-up Package (Initiate Order) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy_package') {
    if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $packId = (int)($_POST['package_id'] ?? 0);
        $selectedPack = null;

        foreach ($GLOBALS['TOPUP_PACKAGES'] as $p) {
            if ($p['id'] === $packId) {
                $selectedPack = $p;
                break;
            }
        }

        if (!$selectedPack) {
            $error = 'Invalid package selected.';
        } else {
            $orderId = 'ORD-' . strtoupper(bin2hex(random_bytes(6)));
            
            // Record pending order
            $insOrder = $pdo->prepare("
                INSERT INTO web_topup_orders (order_id, username, gateway, package_name, amount, currency, points, status, ip_address)
                VALUES (:oid, :u, 'paypal', :pname, :amt, :curr, :pts, 'pending', :ip)
            ");
            $insOrder->execute([
                ':oid'   => $orderId,
                ':u'     => $user['username'],
                ':pname' => $selectedPack['name'],
                ':amt'   => $selectedPack['price'],
                ':curr'  => $selectedPack['currency'],
                ':pts'   => $selectedPack['points'],
                ':ip'    => Security::getClientIP()
            ]);

            // In production, redirects to PayPal / Stripe checkout URL
            // For now, provide instant simulation option or payment modal:
            $success = sprintf('Order #%s created for %s. Complete your payment to receive %d points.', $orderId, $selectedPack['name'], $selectedPack['points']);
        }
    }
}

// Query current live game points
$gameAcc = GameRelay::checkUser($user['username']);
$currentPoints = $gameAcc['user']['userpoint'] ?? 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin: 30px auto 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px;">Recharge Game Points</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Instant top-up delivery directly into your game character inventory</p>
        </div>
        <div class="card" style="padding: 12px 20px; display: flex; align-items: center; gap: 14px;">
            <i class="fa-solid fa-wallet" style="font-size: 24px; color: var(--accent-gold);"></i>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Current Balance</div>
                <div style="font-size: 20px; font-weight: 800; color: #fff;"><?= number_format($currentPoints) ?> Points</div>
            </div>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Redeem PIN Voucher Card -->
    <div class="card" style="margin-bottom: 40px; background: linear-gradient(135deg, rgba(18, 24, 38, 0.95), rgba(26, 35, 56, 0.85));">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
            <i class="fa-solid fa-ticket-simple" style="font-size: 26px; color: var(--primary);"></i>
            <div>
                <h3 style="font-size: 18px;">Redeem Prepaid Voucher / PIN Code</h3>
                <p style="color: var(--text-muted); font-size: 13px;">Got an event voucher or promo code? Enter it below for instant credit.</p>
            </div>
        </div>

        <form method="POST" action="topup.php" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <?= Security::csrfField() ?>
            <input type="hidden" name="action" value="redeem_pin">

            <div style="flex: 1; min-width: 260px;">
                <input type="text" name="pin_code" class="form-control" placeholder="e.g. RAN-STARTER-100P" required
                       style="text-transform: uppercase; font-family: monospace; font-size: 15px; letter-spacing: 1px;">
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                <i class="fa-solid fa-bolt"></i> Claim Points
            </button>
        </form>
    </div>

    <!-- Available Packages Grid -->
    <h2 style="font-size: 22px; margin-bottom: 20px;"><i class="fa-solid fa-box-open" style="color: var(--accent-gold);"></i> Point Packages</h2>
    
    <div class="grid-4" style="margin-bottom: 40px;">
        <?php foreach ($GLOBALS['TOPUP_PACKAGES'] as $pack): ?>
            <div class="pack-card">
                <div class="pack-bonus"><?= htmlspecialchars($pack['bonus']) ?></div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text-light);"><?= htmlspecialchars($pack['name']) ?></div>
                <div class="pack-points"><?= number_format($pack['points']) ?> P</div>
                <div class="pack-price">$<?= number_format($pack['price'], 2) ?> <?= htmlspecialchars($pack['currency']) ?></div>

                <form method="POST" action="topup.php">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="action" value="buy_package">
                    <input type="hidden" name="package_id" value="<?= (int)$pack['id'] ?>">
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa-brands fa-paypal"></i> Buy Now
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Payment Notice -->
    <div class="card" style="font-size: 13px; color: var(--text-muted);">
        <h4 style="color: #fff; margin-bottom: 8px;"><i class="fa-solid fa-shield-halved" style="color: var(--success);"></i> Safe & Secure Transactions</h4>
        <p>
            All top-up requests are routed via an isolated HMAC-SHA256 authenticated relay to the game server. Points are delivered automatically within seconds. If you encounter any delays, submit your transaction ID to our GM team on Discord.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
