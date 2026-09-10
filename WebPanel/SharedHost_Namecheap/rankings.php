<?php
/**
 * RanOnline Web Panel — Character & Guild Rankings
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Server Rankings — ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/game_relay.php';
require_once __DIR__ . '/includes/constants.php';

// Fetch rankings from Game Server Relay
$rankingsData = GameRelay::getRankings(50);
$charRankings = $rankingsData['characters'] ?? [];
$guildRankings = $rankingsData['guilds'] ?? [];

$viewTab = $_GET['tab'] ?? 'characters';
?>

<div class="container" style="margin: 30px auto 60px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="font-size: 32px;"><i class="fa-solid fa-trophy" style="color: var(--accent-gold);"></i> Hall of Fame</h1>
        <p style="color: var(--text-muted); font-size: 14px;">The top contenders, academy elites, and dominant guilds of <?= htmlspecialchars(APP_NAME) ?></p>
    </div>

    <!-- Tab Selector -->
    <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 30px;">
        <a href="rankings.php?tab=characters" class="btn <?= ($viewTab === 'characters') ? 'btn-primary' : 'btn-outline' ?>">
            <i class="fa-solid fa-user-ninja"></i> Top Characters
        </a>
        <a href="rankings.php?tab=guilds" class="btn <?= ($viewTab === 'guilds') ? 'btn-primary' : 'btn-outline' ?>">
            <i class="fa-solid fa-shield-halved"></i> Top Guilds / Clubs
        </a>
    </div>

    <!-- Character Rankings Table -->
    <?php if ($viewTab === 'characters'): ?>
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-medal" style="color: var(--primary);"></i> Top 50 Players
            </h3>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>Character Name</th>
                            <th>Class</th>
                            <th>Campus</th>
                            <th>Level</th>
                            <th>Guild</th>
                            <th>PK Kills</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($charRankings)): ?>
                            <?php foreach ($charRankings as $index => $cha): ?>
                                <tr>
                                    <td>
                                        <?php if ($index === 0): ?>
                                            <span style="color: #ffd700; font-size: 16px; font-weight: 800;"><i class="fa-solid fa-crown"></i> 1</span>
                                        <?php elseif ($index === 1): ?>
                                            <span style="color: #c0c0c0; font-size: 16px; font-weight: 800;"><i class="fa-solid fa-medal"></i> 2</span>
                                        <?php elseif ($index === 2): ?>
                                            <span style="color: #cd7f32; font-size: 16px; font-weight: 800;"><i class="fa-solid fa-award"></i> 3</span>
                                        <?php else: ?>
                                            <span style="font-weight: 600; color: var(--text-muted);"><?= $index + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($cha['chaname'] ?? 'Unknown') ?></strong></td>
                                    <td><?= htmlspecialchars(getClassName((int)($cha['chaclass'] ?? 0))) ?></td>
                                    <td>
                                        <span style="color: <?= getSchoolColor((int)($cha['chaschool'] ?? 0)) ?>; font-weight: 600;">
                                            <?= htmlspecialchars(getSchoolName((int)($cha['chaschool'] ?? 0))) ?>
                                        </span>
                                    </td>
                                    <td><strong style="color: var(--primary);">Lv. <?= (int)($cha['chalevel'] ?? 1) ?></strong></td>
                                    <td><?= htmlspecialchars($cha['chaguname'] ?? '-') ?></td>
                                    <td style="color: var(--accent); font-weight: 700;"><?= (int)($cha['chapkscore'] ?? 0) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No character ranking data available yet. Start your training today!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- Guild Rankings Table -->
    <?php else: ?>
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-flag" style="color: var(--accent-gold);"></i> Top Clubs & Guilds
            </h3>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">Rank</th>
                            <th>Guild Name</th>
                            <th>Club Master</th>
                            <th>Club Wars Won</th>
                            <th>Club Wars Lost</th>
                            <th>Rating Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($guildRankings)): ?>
                            <?php foreach ($guildRankings as $index => $guild): ?>
                                <tr>
                                    <td>
                                        <span style="font-weight: 700; color: <?= ($index < 3) ? 'var(--accent-gold)' : 'var(--text-muted)' ?>;">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </td>
                                    <td><strong><?= htmlspecialchars($guild['guname'] ?? 'Unknown') ?></strong></td>
                                    <td><?= htmlspecialchars($guild['gumaster'] ?? '-') ?></td>
                                    <td style="color: var(--success); font-weight: 700;"><?= (int)($guild['gubattlewin'] ?? 0) ?></td>
                                    <td style="color: var(--danger);"><?= (int)($guild['gubattlelose'] ?? 0) ?></td>
                                    <td><strong style="color: var(--primary);"><?= (int)($guild['gurank'] ?? 0) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">
                                    No guild ranking records yet. Form a club in-game to participate!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
