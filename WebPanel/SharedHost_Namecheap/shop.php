<?php
/**
 * RanOnline Web Panel — Item Mall / Web Shop
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Item Mall — ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/game_relay.php';

// Featured Item Catalog
$shopItems = [
    [
        'id' => 1,
        'name' => 'Back To School Costume Box',
        'category' => 'Costumes',
        'price' => 350,
        'icon' => 'fa-shirt',
        'desc' => 'Permanent high-school uniform costume set granting +10 All Stats and 5% Movement Speed.'
    ],
    [
        'id' => 2,
        'name' => 'Oblivion Potion (E)',
        'category' => 'Potions',
        'price' => 150,
        'icon' => 'fa-flask-vial',
        'desc' => 'Resets all allocated skill points and stat points allowing full character re-build.'
    ],
    [
        'id' => 3,
        'name' => 'Super Burr (x10)',
        'category' => 'Refining',
        'price' => 200,
        'icon' => 'fa-gem',
        'desc' => 'Guaranteed protection stone for equipment upgrading between +7 to +10.'
    ],
    [
        'id' => 4,
        'name' => 'VIP Club Pass (30 Days)',
        'category' => 'Services',
        'price' => 500,
        'icon' => 'fa-crown',
        'desc' => 'Grants +50% EXP boost, remote locker access, and exclusive VIP chat crown badge.'
    ],
    [
        'id' => 5,
        'name' => 'Megaphone (x50)',
        'category' => 'Utility',
        'price' => 50,
        'icon' => 'fa-bullhorn',
        'desc' => 'Broadcast messages across all campuses and maps on the entire server.'
    ],
    [
        'id' => 6,
        'name' => 'Motorcycle Key (Night Hawk)',
        'category' => 'Vehicles',
        'price' => 800,
        'icon' => 'fa-motorcycle',
        'desc' => 'Fast combat vehicle mounting high acceleration with custom dual exhaust sound.'
    ],
];
?>

<div class="container" style="margin: 30px auto 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 28px;"><i class="fa-solid fa-store" style="color: var(--primary);"></i> In-Game Item Mall</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Browse items available in the in-game Item Shop using your Game Points</p>
        </div>
        <div>
            <a href="topup.php" class="btn btn-primary">
                <i class="fa-solid fa-coins"></i> Get Points
            </a>
        </div>
    </div>

    <!-- Shop Grid -->
    <div class="grid-3">
        <?php foreach ($shopItems as $item): ?>
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(0, 180, 216, 0.12); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 22px; color: var(--primary);">
                            <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i>
                        </div>
                        <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); background: rgba(255,255,255,0.04); padding: 4px 8px; border-radius: 4px;">
                            <?= htmlspecialchars($item['category']) ?>
                        </span>
                    </div>

                    <h3 style="font-size: 17px; margin-bottom: 8px; color: var(--text-light);"><?= htmlspecialchars($item['name']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                        <?= htmlspecialchars($item['desc']) ?>
                    </p>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 14px; border-top: 1px solid rgba(64, 93, 140, 0.2);">
                    <div style="font-size: 18px; font-weight: 800; color: var(--accent-gold);">
                        <i class="fa-solid fa-coins"></i> <?= number_format($item['price']) ?> P
                    </div>
                    <span style="font-size: 12px; color: var(--text-muted);">Purchase in-game</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
