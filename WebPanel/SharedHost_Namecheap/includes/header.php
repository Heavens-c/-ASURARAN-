<?php
/**
 * RanOnline Web Panel — Header & Navigation Component
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';

// Enforce Emergency Kill Switch / Maintenance Guard
Settings::enforceMaintenanceGuard();

Security::sendSecurityHeaders();
$currentUser = Auth::user();
$isAdmin = ($currentUser && (int)($currentUser['user_type'] ?? 1) >= 19);

// Active nav highlight helper
$currentScript = basename($_SERVER['PHP_SELF']);
function isActive(string $file, string $currentScript): string {
    return ($file === $currentScript) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="RanOnline Private Server - 8 Balanced Classes, Dynamic Club Wars, Dedicated Economy.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Theme -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Sticky Navigation -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand-logo">
                <i class="fa-solid fa-fire-flame-curved" style="color: var(--primary);"></i>
                <?= htmlspecialchars(APP_NAME) ?>
                <span class="brand-badge">Official</span>
            </a>

            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link <?= isActive('index.php', $currentScript) ?>"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="rankings.php" class="nav-link <?= isActive('rankings.php', $currentScript) ?>"><i class="fa-solid fa-trophy"></i> Rankings</a></li>
                <li><a href="shop.php" class="nav-link <?= isActive('shop.php', $currentScript) ?>"><i class="fa-solid fa-store"></i> Item Mall</a></li>
                <li><a href="topup.php" class="nav-link <?= isActive('topup.php', $currentScript) ?>"><i class="fa-solid fa-coins"></i> Top-Up</a></li>
                <li><a href="download.php" class="nav-link <?= isActive('download.php', $currentScript) ?>"><i class="fa-solid fa-download"></i> Download</a></li>
            </ul>

            <div class="nav-actions">
                <?php if ($currentUser): ?>
                    <?php if ($isAdmin): ?>
                        <a href="admin/index.php" class="btn btn-accent" style="font-size: 13px; padding: 8px 12px;" title="Admin Console">
                            <i class="fa-solid fa-shield-halved"></i> Admin
                        </a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-outline" style="font-size: 13px; padding: 8px 14px;">
                        <i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars($currentUser['username']) ?>
                    </a>
                    <a href="logout.php" class="btn btn-outline" style="font-size: 13px; padding: 8px 12px; color: var(--danger);" title="Logout">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Log In
                    </a>
                    <a href="register.php" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                        <i class="fa-solid fa-user-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (Settings::isMaintenanceActive()): ?>
        <div style="background: #eb4d4b; color: #fff; text-align: center; padding: 10px 16px; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
            <i class="fa-solid fa-triangle-exclamation"></i> <strong>OPS KILL SWITCH IS ON:</strong> Public visitors are currently redirected to the Server Maintenance page. (Admins have full bypass)
        </div>
    <?php endif; ?>

    <!-- Global Flash Messages -->
    <div class="container" style="margin-top: 20px;">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>
