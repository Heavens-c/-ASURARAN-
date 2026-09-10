<?php
/**
 * RanOnline Web Panel — Database Connection Wrapper
 * Uses PDO with strict prepared statements, exceptions, and UTF-8 encoding.
 * Supports MySQL (Production on Namecheap) with automated local SQLite fallback for dev preview.
 */

require_once __DIR__ . '/config.php';

class DB {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';

            if ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                try {
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                } catch (PDOException $e) {
                    // If running locally and MySQL is unavailable, auto-fallback to local SQLite
                    error_log('MySQL connection failed, falling back to local SQLite preview: ' . $e->getMessage());
                    self::$instance = self::connectSQLite();
                }
            } else {
                self::$instance = self::connectSQLite();
            }
        }
        return self::$instance;
    }

    private static function connectSQLite(): PDO {
        $dataDir = __DIR__ . '/../data';
        if (!is_dir($dataDir)) {
            @mkdir($dataDir, 0777, true);
        }
        $dbPath = $dataDir . '/local_preview.sqlite';
        $isNew = !file_exists($dbPath);

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($isNew) {
            self::seedSQLite($pdo);
        }
        return $pdo;
    }

    private static function seedSQLite(PDO $pdo): void {
        $schema = "
            CREATE TABLE IF NOT EXISTS web_accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                pincode TEXT DEFAULT '',
                game_usernum INTEGER DEFAULT NULL,
                user_type INTEGER DEFAULT 1,
                is_blocked INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login_at DATETIME DEFAULT NULL,
                last_login_ip TEXT DEFAULT NULL
            );

            CREATE TABLE IF NOT EXISTS web_password_resets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                token TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                used INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS web_topup_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id TEXT UNIQUE NOT NULL,
                username TEXT NOT NULL,
                gateway TEXT NOT NULL,
                package_name TEXT NOT NULL,
                amount REAL NOT NULL,
                currency TEXT DEFAULT 'USD',
                points INTEGER NOT NULL,
                status TEXT DEFAULT 'pending',
                transaction_id TEXT DEFAULT NULL,
                game_credited INTEGER DEFAULT 0,
                credited_at DATETIME DEFAULT NULL,
                ip_address TEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS web_pin_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                pin_code TEXT UNIQUE NOT NULL,
                points INTEGER NOT NULL,
                status TEXT DEFAULT 'active',
                used_by TEXT DEFAULT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS web_news (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                category TEXT DEFAULT 'Announcement',
                content TEXT NOT NULL,
                author TEXT DEFAULT 'Admin',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS web_rate_limits (
                ip_address TEXT NOT NULL,
                action_key TEXT NOT NULL,
                hits INTEGER DEFAULT 1,
                first_attempt INTEGER NOT NULL,
                PRIMARY KEY (ip_address, action_key)
            );

            CREATE TABLE IF NOT EXISTS web_settings (
                setting_key TEXT PRIMARY KEY,
                setting_value TEXT NOT NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ";
        $pdo->exec($schema);

        // Seed default settings
        $settings = [
            'maintenance_mode'    => '0',
            'maintenance_title'   => 'OPS SERVER MAINTENANCE IN PROGRESS',
            'maintenance_message' => 'The game server and web systems are temporarily offline while our DevOps engineers perform scheduled server maintenance, security upgrades, and database optimizations. All player accounts and balances are safe. We will be back online shortly!',
            'maintenance_eta'     => 'Estimated completion: within 1 - 2 hours',
            'maintenance_discord' => 'https://discord.gg/yourserver'
        ];

        $insSet = $pdo->prepare("REPLACE INTO web_settings (setting_key, setting_value) VALUES (:k, :v)");
        foreach ($settings as $k => $v) {
            $insSet->execute([':k' => $k, ':v' => $v]);
        }

        // Seed default Admin account (admin / admin123)
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $insAdmin = $pdo->prepare("
            INSERT OR IGNORE INTO web_accounts (username, password_hash, email, pincode, user_type, created_at)
            VALUES ('admin', :p, 'admin@ranonline.local', '1234', 19, CURRENT_TIMESTAMP)
        ");
        $insAdmin->execute([':p' => $adminPass]);

        // Seed Sample Vouchers
        $vouchers = [
            ['RAN-STARTER-100P', 100],
            ['RAN-OPENING-500P', 500],
            ['RAN-VIPPROMO-1000', 1000]
        ];
        $insV = $pdo->prepare("INSERT OR IGNORE INTO web_pin_codes (pin_code, points, status) VALUES (:c, :p, 'active')");
        foreach ($vouchers as $v) {
            $insV->execute([':c' => $v[0], ':p' => $v[1]]);
        }

        // Seed Sample News
        $news = [
            ['Welcome to RanOnline Server!', 'Announcement', 'Welcome to our newly upgraded server! Experience classic Ran gameplay with modern stability and fair gameplay. Join our Discord for guides and events.'],
            ['Official Grand Opening & Events', 'Event', 'Grand Opening Event is now live! Level up rush, Club Wars kick-off bonuses, and double points top-up promo running all week.']
        ];
        $insN = $pdo->prepare("INSERT INTO web_news (title, category, content, author, created_at) VALUES (:t, :cat, :c, 'Staff', CURRENT_TIMESTAMP)");
        foreach ($news as $n) {
            $insN->execute([':t' => $n[0], ':cat' => $n[1], ':c' => $n[2]]);
        }
    }
}
