-- ===========================================
-- PhotoGallery CMS - Database Schema
-- ===========================================

-- Create database if not exists


-- Users table for authentication and management
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin', 'user', 'moderator') DEFAULT 'user',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` TIMESTAMP NULL,
    `login_attempts` INT DEFAULT 0,
    `locked_until` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Images table for storing image metadata
CREATE TABLE IF NOT EXISTS `images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `filename` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `favorite` TINYINT(1) NOT NULL DEFAULT 0,
    `super_promoted` TINYINT(1) NOT NULL DEFAULT 0,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `file_size` BIGINT DEFAULT NULL,
    `mime_type` VARCHAR(100) DEFAULT NULL,
    `width` INT DEFAULT NULL,
    `height` INT DEFAULT NULL,
    `webp_path` VARCHAR(255) DEFAULT NULL,
    `thumbnail_path` VARCHAR(255) DEFAULT NULL,
    `exif_removed` TINYINT(1) NOT NULL DEFAULT 0,
    `exif_data` JSON DEFAULT NULL,
    `tags` JSON DEFAULT NULL,
    `category_id` INT DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `views` INT DEFAULT 0,
    `downloads` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_filename` (`filename`),
    INDEX `idx_favorite` (`favorite`),
    INDEX `idx_super_promoted` (`super_promoted`),
    INDEX `idx_featured` (`featured`),
    INDEX `idx_category_id` (`category_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_views` (`views`),
    INDEX `idx_downloads` (`downloads`),
    FULLTEXT `idx_search` (`filename`, `description`, `alt_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table for organizing images
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_sort_order` (`sort_order`),
    INDEX `idx_is_active` (`is_active`),
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tags table for image tagging
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `usage_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_usage_count` (`usage_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Image tags relationship table
CREATE TABLE IF NOT EXISTS `image_tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `image_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_image_tag` (`image_id`, `tag_id`),
    INDEX `idx_image_id` (`image_id`),
    INDEX `idx_tag_id` (`tag_id`),
    FOREIGN KEY (`image_id`) REFERENCES `images`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table for user sessions
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_last_activity` (`last_activity`),
    INDEX `idx_ip_address` (`ip_address`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate limiting table
CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `endpoint` VARCHAR(255) NOT NULL,
    `requests_count` INT DEFAULT 1,
    `window_start` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_endpoint` (`ip_address`, `endpoint`),
    INDEX `idx_window_start` (`window_start`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table for application configuration
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_type` ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    `description` TEXT DEFAULT NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`),
    INDEX `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs table for system logging
CREATE TABLE IF NOT EXISTS `logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('debug', 'info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    `message` TEXT NOT NULL,
    `context` JSON DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_level` (`level`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API keys table for external integrations
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `key_hash` VARCHAR(255) NOT NULL,
    `permissions` JSON DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_used` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key_hash` (`key_hash`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Statistics table for tracking usage
CREATE TABLE IF NOT EXISTS `statistics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` DATE NOT NULL,
    `page_views` INT DEFAULT 0,
    `unique_visitors` INT DEFAULT 0,
    `image_views` INT DEFAULT 0,
    `image_downloads` INT DEFAULT 0,
    `uploads` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_date` (`date`),
    INDEX `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT IGNORE INTO `users` (`username`, `password_hash`, `role`, `is_active`) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert default categories
INSERT IGNORE INTO `categories` (`name`, `slug`, `description`, `sort_order`) VALUES
('Uncategorized', 'uncategorized', 'Default category for images', 0),
('Nature', 'nature', 'Nature and landscape photography', 1),
('Portraits', 'portraits', 'Portrait photography', 2),
('Architecture', 'architecture', 'Architectural photography', 3),
('Street', 'street', 'Street photography', 4),
('Abstract', 'abstract', 'Abstract and artistic photography', 5);

-- Insert default settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_public`) VALUES
('app_name', 'PhotoGallery CMS', 'string', 'Application name', 1),
('app_language', 'cs', 'string', 'Default language', 1),
('app_theme', 'auto', 'string', 'Default theme', 1),
('app_timezone', 'Europe/Prague', 'string', 'Default timezone', 1),
('image_quality', '85', 'integer', 'Image quality for WebP conversion', 0),
('max_upload_size', '10485760', 'integer', 'Maximum upload size in bytes', 0),
('auto_convert_webp', '1', 'boolean', 'Automatically convert images to WebP', 0),
('remove_exif', '1', 'boolean', 'Remove EXIF data from images', 0),
('enable_rate_limiting', '1', 'boolean', 'Enable rate limiting', 0),
('rate_limit_requests', '100', 'integer', 'Maximum requests per window', 0),
('rate_limit_window', '3600', 'integer', 'Rate limit window in seconds', 0),
('enable_logging', '1', 'boolean', 'Enable system logging', 0),
('log_level', 'info', 'string', 'Minimum log level to record', 0),
('max_log_files', '30', 'integer', 'Maximum number of log files to keep', 0),
('enable_statistics', '1', 'boolean', 'Enable usage statistics', 0),
('privacy_policy_url', '', 'string', 'Privacy policy URL', 1),
('terms_of_service_url', '', 'string', 'Terms of service URL', 1),
('contact_email', '', 'string', 'Contact email address', 1),
('social_links', '{"instagram":"","facebook":"","twitter":"","youtube":""}', 'json', 'Social media links', 1),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode', 0),
('maintenance_message', 'Site is under maintenance. Please check back later.', 'string', 'Maintenance mode message', 1);

-- Insert default tags
INSERT IGNORE INTO `tags` (`name`, `slug`, `description`) VALUES
('landscape', 'landscape', 'Landscape photography'),
('portrait', 'portrait', 'Portrait photography'),
('nature', 'nature', 'Nature photography'),
('urban', 'urban', 'Urban photography'),
('black-and-white', 'black-and-white', 'Black and white photography'),
('color', 'color', 'Color photography'),
('abstract', 'abstract', 'Abstract photography'),
('street', 'street', 'Street photography'),
('architecture', 'architecture', 'Architectural photography'),
('macro', 'macro', 'Macro photography');

-- Create indexes for better performance
CREATE INDEX `idx_images_created_at_desc` ON `images` (`created_at` DESC);
CREATE INDEX `idx_images_views_desc` ON `images` (`views` DESC);
CREATE INDEX `idx_images_downloads_desc` ON `images` (`downloads` DESC);
CREATE INDEX `idx_logs_level_created_at` ON `logs` (`level`, `created_at`);
CREATE INDEX `idx_statistics_date_desc` ON `statistics` (`date` DESC);

-- Add foreign key constraints
ALTER TABLE `images` 
ADD CONSTRAINT `fk_images_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_images_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL;

-- Create views for common queries
CREATE OR REPLACE VIEW `v_image_stats` AS
SELECT 
    i.id,
    i.filename,
    i.original_name,
    i.description,
    i.favorite,
    i.super_promoted,
    i.featured,
    i.views,
    i.downloads,
    i.created_at,
    c.name as category_name,
    c.slug as category_slug,
    GROUP_CONCAT(t.name SEPARATOR ', ') as tags
FROM `images` i
LEFT JOIN `categories` c ON i.category_id = c.id
LEFT JOIN `image_tags` it ON i.id = it.image_id
LEFT JOIN `tags` t ON it.tag_id = t.id
GROUP BY i.id;

CREATE OR REPLACE VIEW `v_popular_images` AS
SELECT 
    id,
    filename,
    original_name,
    description,
    views,
    downloads,
    (views + downloads * 2) as popularity_score
FROM `images`
WHERE is_active = 1
ORDER BY popularity_score DESC;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE `sp_cleanup_old_sessions`()
BEGIN
    DELETE FROM `sessions` WHERE `last_activity` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR));
END//

CREATE PROCEDURE `sp_cleanup_old_logs`()
BEGIN
    DELETE FROM `logs` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 30 DAY);
END//

CREATE PROCEDURE `sp_cleanup_rate_limits`()
BEGIN
    DELETE FROM `rate_limits` WHERE `window_start` < DATE_SUB(NOW(), INTERVAL 1 HOUR);
END//

CREATE PROCEDURE `sp_update_image_stats`(IN image_id INT)
BEGIN
    UPDATE `images` 
    SET `views` = `views` + 1 
    WHERE `id` = image_id;
END//

DELIMITER ;

-- Create events for automatic cleanup
CREATE EVENT IF NOT EXISTS `evt_cleanup_sessions`
ON SCHEDULE EVERY 1 HOUR
DO CALL `sp_cleanup_old_sessions`();

CREATE EVENT IF NOT EXISTS `evt_cleanup_logs`
ON SCHEDULE EVERY 1 DAY
DO CALL `sp_cleanup_old_logs`();

CREATE EVENT IF NOT EXISTS `evt_cleanup_rate_limits`
ON SCHEDULE EVERY 1 HOUR
DO CALL `sp_cleanup_rate_limits`();

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON `photogallery`.* TO 'your_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Show table creation status
SHOW TABLES;

-- Show table structure
DESCRIBE `users`;
DESCRIBE `images`;
DESCRIBE `categories`;
DESCRIBE `tags`;
DESCRIBE `image_tags`;
DESCRIBE `sessions`;
DESCRIBE `rate_limits`;
DESCRIBE `settings`;
DESCRIBE `logs`;
DESCRIBE `api_keys`;
DESCRIBE `statistics`;

echo "Database schema created successfully!"
echo "Default admin user: admin (password: password)"
echo "Please change the default password after first login!"

