<?php
/**
 * RanOnline Web Panel — Emergency Kill Switch / Server Maintenance Splash
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/auth.php';

// If maintenance is turned off and visitor is here, send back to home
if (!Settings::isMaintenanceActive()) {
    header('Location: index.php');
    exit;
}

$title   = Settings::get('maintenance_title', 'OPS SERVER MAINTENANCE IN PROGRESS');
$message = Settings::get('maintenance_message', 'Our technical staff is performing scheduled maintenance. Services will resume shortly.');
$eta     = Settings::get('maintenance_eta', 'Estimated completion: within 1 to 2 hours');
$discord = Settings::get('maintenance_discord', DISCORD_URL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Maintenance — <?= htmlspecialchars(APP_NAME) ?></title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Theme CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .maint-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            background: radial-gradient(circle at 50% 30%, rgba(255, 71, 87, 0.15) 0%, rgba(10, 13, 20, 0.98) 70%);
        }
        .maint-card {
            max-width: 640px;
            width: 100%;
            text-align: center;
            padding: 48px 36px;
            border-color: rgba(255, 71, 87, 0.35);
            box-shadow: 0 0 50px rgba(255, 71, 87, 0.2);
        }
        .pulse-icon-container {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 71, 87, 0.12);
            border: 2px solid #ff4757;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 38px;
            color: #ff4757;
            animation: pulse-ring 2.5s infinite;
        }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(255, 71, 87, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); }
        }
    </style>
</head>
<body>

<div class="maint-wrapper">
    <div class="card maint-card">
        <div class="pulse-icon-container">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>

        <div style="display: inline-block; background: rgba(255, 71, 87, 0.2); color: #ff6b81; font-weight: 800; font-size: 11px; padding: 4px 12px; border-radius: 50px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px;">
            <i class="fa-solid fa-triangle-exclamation"></i> OPS KILL SWITCH ACTIVE
        </div>

        <h1 style="font-size: 30px; margin-bottom: 16px; color: #fff; line-height: 1.25;">
            <?= htmlspecialchars($title) ?>
        </h1>

        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin-bottom: 28px;">
            <?= nl2br(htmlspecialchars($message)) ?>
        </p>

        <?php if (!empty($eta)): ?>
            <div style="background: rgba(0, 180, 216, 0.08); border: 1px solid rgba(0, 180, 216, 0.25); border-radius: var(--radius-md); padding: 14px 20px; margin-bottom: 30px; display: inline-flex; align-items: center; gap: 10px; color: var(--primary); font-weight: 600; font-size: 14px;">
                <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($eta) ?>
            </div>
        <?php endif; ?>

        <?php 
        $currentUser = Auth::user(); 
        $isAdmin = ($currentUser && (int)($currentUser['user_type'] ?? 1) >= 19);
        ?>

        <?php if ($isAdmin): ?>
            <div style="background: rgba(46, 213, 115, 0.15); border: 1px solid rgba(46, 213, 115, 0.4); border-radius: var(--radius-md); padding: 14px; margin-bottom: 24px; text-align: center;">
                <div style="color: #2ed573; font-weight: 700; font-size: 13px; margin-bottom: 8px;">
                    <i class="fa-solid fa-user-shield"></i> You are logged in as Administrator (<?= htmlspecialchars($currentUser['username']) ?>)
                </div>
                <a href="admin/index.php" class="btn btn-primary" style="background: #2ed573; border-color: #2ed573; font-size: 13px; padding: 8px 18px;">
                    <i class="fa-solid fa-power-off"></i> Open Admin Console & Turn OFF Kill Switch
                </a>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 30px;">
            <a href="<?= htmlspecialchars($discord) ?>" target="_blank" rel="noopener" class="btn btn-primary" style="background: #5865F2; border-color: #5865F2;">
                <i class="fa-brands fa-discord"></i> Join Discord for Updates
            </a>
            <a href="index.php" class="btn btn-outline">
                <i class="fa-solid fa-rotate-right"></i> Check Status Again
            </a>
        </div>

        <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 20px; font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; align-items: center;">
            <span>&copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?></span>
            <?php if ($isAdmin): ?>
                <a href="admin/index.php" style="color: var(--primary); font-weight: 700;">Admin Console &rarr;</a>
            <?php else: ?>
                <a href="login.php" style="color: var(--text-muted); text-decoration: underline;">Admin Login & Bypass &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
