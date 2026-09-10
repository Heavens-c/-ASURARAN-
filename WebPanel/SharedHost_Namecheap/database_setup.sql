-- ============================================================================
-- RanOnline Web Panel — MySQL Schema for Namecheap Shared Hosting (cPanel)
-- Database Engine: InnoDB, Charset: utf8mb4
-- ============================================================================

CREATE TABLE IF NOT EXISTS `web_accounts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(32) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `pincode` VARCHAR(10) DEFAULT '',
    `game_usernum` INT DEFAULT NULL,
    `user_type` INT DEFAULT 1,
    `is_blocked` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `last_login_at` DATETIME DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `web_password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(32) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_token` (`token`),
    INDEX `idx_user_expires` (`username`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `web_topup_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` VARCHAR(64) NOT NULL UNIQUE,
    `username` VARCHAR(32) NOT NULL,
    `gateway` VARCHAR(32) NOT NULL,
    `package_name` VARCHAR(64) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(8) DEFAULT 'USD',
    `points` INT NOT NULL,
    `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    `transaction_id` VARCHAR(128) DEFAULT NULL,
    `game_credited` TINYINT(1) DEFAULT 0,
    `credited_at` DATETIME DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_id` (`order_id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `web_pin_codes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pin_code` VARCHAR(24) NOT NULL UNIQUE,
    `points` INT NOT NULL,
    `status` ENUM('active', 'used', 'expired') DEFAULT 'active',
    `used_by` VARCHAR(32) DEFAULT NULL,
    `used_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pin_code` (`pin_code`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `web_news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(32) DEFAULT 'Announcement',
    `content` TEXT NOT NULL,
    `author` VARCHAR(32) DEFAULT 'Admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `web_rate_limits` (
    `ip_address` VARCHAR(45) NOT NULL,
    `action_key` VARCHAR(32) NOT NULL,
    `hits` INT DEFAULT 1,
    `first_attempt` INT NOT NULL,
    PRIMARY KEY (`ip_address`, `action_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Server Settings & Kill Switch / Maintenance Table
CREATE TABLE IF NOT EXISTS `web_settings` (
    `setting_key` VARCHAR(64) PRIMARY KEY,
    `setting_value` TEXT NOT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initialize Settings with Defaults
INSERT IGNORE INTO `web_settings` (`setting_key`, `setting_value`) VALUES
('maintenance_mode', '0'),
('maintenance_title', 'OPS SERVER MAINTENANCE IN PROGRESS'),
('maintenance_message', 'The game server and web systems are temporarily offline while our DevOps engineers perform scheduled server maintenance, security upgrades, and database optimizations. All player accounts and balances are safe. We will be back online shortly!'),
('maintenance_eta', 'Estimated completion: within 1 - 2 hours'),
('maintenance_discord', 'https://discord.gg/yourserver');

-- Sample News
INSERT IGNORE INTO `web_news` (`id`, `title`, `category`, `content`, `author`, `created_at`) VALUES
(1, 'Welcome to RanOnline Server!', 'Announcement', 'Welcome to our newly upgraded server! Experience classic Ran gameplay with modern stability and fair gameplay. Join our Discord for guides and events.', 'Staff', NOW()),
(2, 'Official Grand Opening & Events', 'Event', 'Grand Opening Event is now live! Level up rush, Club Wars kick-off bonuses, and double points top-up promo running all week.', 'Staff', NOW());

-- Sample Starter Top-up Vouchers (Can be redeemed once by players)
INSERT IGNORE INTO `web_pin_codes` (`pin_code`, `points`, `status`) VALUES
('RAN-STARTER-100P', 100, 'active'),
('RAN-OPENING-500P', 500, 'active'),
('RAN-VIPPROMO-1000', 1000, 'active');
