<?php
/**
 * PhotoGallery CMS - Main Configuration
 * 
 * This file contains all configuration settings for the application
 */

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Database configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'photogallery');

// Application configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'PhotoGallery CMS');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_LANG', $_ENV['APP_LANG'] ?? 'cs');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Europe/Prague');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');

// Security configuration
define('ADMIN_USERNAME', $_ENV['ADMIN_USERNAME'] ?? 'admin');
define('ADMIN_PASSWORD', $_ENV['ADMIN_PASSWORD'] ?? 'admin123');
define('SECRET_KEY', $_ENV['SECRET_KEY'] ?? 'your_random_secret_key_32_chars_long');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));
define('CSRF_TOKEN_LIFETIME', (int)($_ENV['CSRF_TOKEN_LIFETIME'] ?? 1800));

// Image processing configuration
define('IMAGE_QUALITY', (int)($_ENV['IMAGE_QUALITY'] ?? 85));
define('MAX_IMAGE_SIZE', (int)($_ENV['MAX_IMAGE_SIZE'] ?? 10485760)); // 10MB
define('ALLOWED_EXTENSIONS', explode(',', $_ENV['ALLOWED_EXTENSIONS'] ?? 'jpg,jpeg,png,gif,webp,bmp'));
define('AUTO_CONVERT_TO_WEBP', filter_var($_ENV['AUTO_CONVERT_TO_WEBP'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('REMOVE_EXIF', filter_var($_ENV['REMOVE_EXIF'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('THUMBNAIL_WIDTH', (int)($_ENV['THUMBNAIL_WIDTH'] ?? 300));
define('THUMBNAIL_HEIGHT', (int)($_ENV['THUMBNAIL_HEIGHT'] ?? 300));

// Rate limiting configuration
define('RATE_LIMIT_REQUESTS', (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 100));
define('RATE_LIMIT_WINDOW', (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 3600));

// Logging configuration
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'info');
define('LOG_FILE', $_ENV['LOG_FILE'] ?? 'logs/app.log');
define('LOG_MAX_FILES', (int)($_ENV['LOG_MAX_FILES'] ?? 30));

// Cache configuration
define('CACHE_ENABLED', filter_var($_ENV['CACHE_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN));
define('CACHE_DRIVER', $_ENV['CACHE_DRIVER'] ?? 'file');
define('CACHE_TTL', (int)($_ENV['CACHE_TTL'] ?? 3600));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
