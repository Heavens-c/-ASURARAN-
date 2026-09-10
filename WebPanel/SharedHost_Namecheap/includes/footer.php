<?php
/**
 * RanOnline Web Panel — Footer Component
 */
require_once __DIR__ . '/config.php';
?>
    <!-- Global Footer -->
    <footer class="footer">
        <div class="container footer-content">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #fff; margin-bottom: 6px;">
                    <i class="fa-solid fa-gamepad" style="color: var(--primary);"></i>
                    <?= htmlspecialchars(APP_NAME) ?>
                </div>
                <p style="color: var(--text-muted); font-size: 13px;">
                    Classic MMORPG Experience. All game trademarks belong to their respective owners.
                </p>
            </div>

            <div style="display: flex; gap: 16px; font-size: 18px;">
                <a href="<?= htmlspecialchars(DISCORD_URL) ?>" target="_blank" rel="noopener" style="color: #5865F2;" title="Join Discord">
                    <i class="fa-brands fa-discord"></i>
                </a>
                <a href="<?= htmlspecialchars(FACEBOOK_URL) ?>" target="_blank" rel="noopener" style="color: #1877F2;" title="Facebook Community">
                    <i class="fa-brands fa-facebook"></i>
                </a>
            </div>
        </div>

        <div class="container" style="margin-top: 24px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; font-size: 12px; color: #576574;">
            &copy; <?= date('Y') ?> <?= htmlspecialchars(APP_NAME) ?>. Hosted on Namecheap Shared Hosting with Protected Windows Game Server Relay.
        </div>
    </footer>

    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
