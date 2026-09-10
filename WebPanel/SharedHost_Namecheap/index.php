<?php
/**
 * RanOnline Web Panel — Landing Page
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home — ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/game_relay.php';

// Fetch recent announcements from MySQL
$newsList = [];
try {
    $pdo = DB::connect();
    $stmt = $pdo->query("SELECT * FROM web_news ORDER BY created_at DESC LIMIT 5");
    $newsList = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Could not load news: " . $e->getMessage());
}

// Check server status via safe relay
$status = GameRelay::getServerStatus();
$isOnline = !empty($status['online']);
$onlineCount = $status['online_players'] ?? 0;
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-tag">
            <i class="fa-solid fa-bolt"></i> <?= htmlspecialchars(GAME_SERVER_RATES) ?>
        </div>
        <h1 class="hero-title">ENTER THE BATTLEFIELD</h1>
        <p class="hero-subtitle">
            Relive the greatest school warfare MMORPG. Master 8 balanced classes, conquer the Sacred Gate, and dominate the weekly Club Wars.
        </p>

        <div class="hero-buttons">
            <a href="download.php" class="btn btn-primary" style="font-size: 16px; padding: 14px 32px;">
                <i class="fa-solid fa-download"></i> Download Client
            </a>
            <?php if (!$currentUser): ?>
                <a href="register.php" class="btn btn-accent" style="font-size: 16px; padding: 14px 32px;">
                    <i class="fa-solid fa-user-plus"></i> Join Now
                </a>
            <?php else: ?>
                <a href="dashboard.php" class="btn btn-outline" style="font-size: 16px; padding: 14px 32px;">
                    <i class="fa-solid fa-user-shield"></i> Player Dashboard
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Status & Info Grid -->
    <div class="grid-3" style="margin-bottom: 40px;">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                <h3 style="font-size: 18px;"><i class="fa-solid fa-server" style="color: var(--primary);"></i> Server Status</h3>
                <span class="status-badge">
                    <span class="status-dot <?= $isOnline ? '' : 'offline' ?>"></span>
                    <?= $isOnline ? 'Online' : 'Offline' ?>
                </span>
            </div>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 10px;">
                Realm: <strong><?= htmlspecialchars(GAME_SERVER_NAME) ?></strong>
            </p>
            <p style="color: var(--text-muted); font-size: 13px;">
                Players In-Game: <strong style="color: var(--success);"><?= (int)$onlineCount ?></strong>
            </p>
        </div>

        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 14px;"><i class="fa-solid fa-sliders" style="color: var(--accent-gold);"></i> Game Features</h3>
            <ul style="list-style: none; color: var(--text-muted); font-size: 13px; line-height: 1.8;">
                <li><i class="fa-solid fa-check" style="color: var(--success);"></i> 8 Classes: Brawler, Swordsman, Archer, Shaman, Extreme, Gunner, Assassin, Tricker</li>
                <li><i class="fa-solid fa-check" style="color: var(--success);"></i> Fully Authoritative Anti-Cheat & PostgreSQL Core</li>
                <li><i class="fa-solid fa-check" style="color: var(--success);"></i> Competitive Club Wars & Tyranny System</li>
            </ul>
        </div>

        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 14px;"><i class="fa-solid fa-shield-halved" style="color: var(--accent);"></i> Secure Economy</h3>
            <p style="color: var(--text-muted); font-size: 13px; line-height: 1.6; margin-bottom: 16px;">
                Our game points top-up and web shop is guarded by an isolated Windows Relay architecture. Your account is 100% safe.
            </p>
            <a href="topup.php" class="btn btn-outline btn-block" style="font-size: 13px;">
                <i class="fa-solid fa-coins"></i> Recharge Points
            </a>
        </div>
    </div>

    <!-- News & Announcements -->
    <div style="margin-bottom: 50px;">
        <h2 style="font-size: 24px; margin-bottom: 20px;"><i class="fa-solid fa-newspaper" style="color: var(--primary);"></i> Latest Announcements</h2>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php if (!empty($newsList)): ?>
                <?php foreach ($newsList as $news): ?>
                    <div class="card" style="padding: 18px 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase;">
                                <?= htmlspecialchars($news['category']) ?>
                            </span>
                            <span style="font-size: 12px; color: var(--text-muted);">
                                <?= date('M d, Y', strtotime($news['created_at'])) ?>
                            </span>
                        </div>
                        <h3 style="font-size: 18px; margin-bottom: 8px;"><?= htmlspecialchars($news['title']) ?></h3>
                        <p style="color: var(--text-muted); font-size: 14px;">
                            <?= nl2br(htmlspecialchars($news['content'])) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card" style="text-align: center; color: var(--text-muted);">
                    No news updates posted yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
