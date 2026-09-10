<?php
/**
 * RanOnline Web Panel — Game Client Download
 */
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Download Client — ' . APP_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin: 30px auto 60px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 34px;"><i class="fa-solid fa-download" style="color: var(--primary);"></i> Download Game Client</h1>
        <p style="color: var(--text-muted); font-size: 15px;">Download the official client to start your adventure in <?= htmlspecialchars(APP_NAME) ?></p>
    </div>

    <!-- Download Mirrors Grid -->
    <div class="grid-3" style="margin-bottom: 40px;">
        <div class="card" style="text-align: center; border-color: var(--primary);">
            <div style="font-size: 36px; color: #d63031; margin-bottom: 12px;">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </div>
            <h3 style="font-size: 20px; margin-bottom: 6px;">MEGA.nz (Primary)</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Full Client Archive (.ZIP) — High Speed</p>
            <a href="<?= htmlspecialchars(CLIENT_DOWNLOAD_URL) ?>" target="_blank" rel="noopener" class="btn btn-primary btn-block">
                <i class="fa-solid fa-download"></i> Download (~2.4 GB)
            </a>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 36px; color: #0984e3; margin-bottom: 12px;">
                <i class="fa-brands fa-google-drive"></i>
            </div>
            <h3 style="font-size: 20px; margin-bottom: 6px;">Google Drive Mirror</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Fast Direct Mirror (.ZIP)</p>
            <a href="#" class="btn btn-outline btn-block">
                <i class="fa-solid fa-download"></i> Mirror Link 1
            </a>
        </div>

        <div class="card" style="text-align: center;">
            <div style="font-size: 36px; color: #00b894; margin-bottom: 12px;">
                <i class="fa-solid fa-file-zipper"></i>
            </div>
            <h3 style="font-size: 20px; margin-bottom: 6px;">MediaFire Mirror</h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Secondary Backup Mirror</p>
            <a href="#" class="btn btn-outline btn-block">
                <i class="fa-solid fa-download"></i> Mirror Link 2
            </a>
        </div>
    </div>

    <!-- Installation Guide & System Requirements -->
    <div class="grid-2">
        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-circle-question" style="color: var(--primary);"></i> Installation Steps
            </h3>
            <ol style="color: var(--text-muted); font-size: 13px; line-height: 1.8; padding-left: 20px;">
                <li>Download the full client archive from any mirror above.</li>
                <li>Extract the `.zip` file to your preferred folder (e.g. <code>C:\Games\RanOnline\</code>).</li>
                <li>Add the game folder to your Antivirus / Windows Defender exclusion list.</li>
                <li>Run <strong>RanOnlineLauncher.exe</strong> or <strong>GameClient.exe</strong> as Administrator.</li>
                <li>Log in with your registered website account credentials.</li>
            </ol>
        </div>

        <div class="card">
            <h3 style="font-size: 18px; margin-bottom: 16px;">
                <i class="fa-solid fa-desktop" style="color: var(--accent-gold);"></i> System Requirements
            </h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Specification</th>
                            <th>Minimum</th>
                            <th>Recommended</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>OS</strong></td>
                            <td>Windows 7 / 8 / 10</td>
                            <td>Windows 10 / 11 64-bit</td>
                        </tr>
                        <tr>
                            <td><strong>CPU</strong></td>
                            <td>Intel Pentium 4 / AMD</td>
                            <td>Intel Core i3 / Ryzen 3 or higher</td>
                        </tr>
                        <tr>
                            <td><strong>RAM</strong></td>
                            <td>2 GB</td>
                            <td>4 GB or higher</td>
                        </tr>
                        <tr>
                            <td><strong>GPU</strong></td>
                            <td>DirectX 9.0c Compatible</td>
                            <td>NVIDIA GeForce / AMD Radeon</td>
                        </tr>
                        <tr>
                            <td><strong>Storage</strong></td>
                            <td>6 GB free space</td>
                            <td>10 GB SSD free space</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
