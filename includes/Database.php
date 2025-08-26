<?php
/**
 * PhotoGallery CMS - Database Class
 * 
 * Modern database wrapper with security features
 */

class Database {
    private $connection;
    private $config;
    private static $instance = null;
    
    public function __construct() {
        $this->config = [
            'host' => DB_HOST,
            'username' => DB_USER,
            'password' => DB_PASS,
            'database' => DB_NAME,
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
        
        $this->connect();
        $this->createTables();
    }
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Create necessary tables if they don't exist
     */
    private function createTables() {
        $tables = [
            'images' => "
                CREATE TABLE IF NOT EXISTS images (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    filename VARCHAR(255) NOT NULL,
                    original_name VARCHAR(255) DEFAULT NULL,
                    description TEXT DEFAULT NULL,
                    favorite TINYINT(1) NOT NULL DEFAULT 0,
                    super_promoted TINYINT(1) NOT NULL DEFAULT 0,
                    file_size INT DEFAULT NULL,
                    mime_type VARCHAR(100) DEFAULT NULL,
                    width INT DEFAULT NULL,
                    height INT DEFAULT NULL,
                    webp_path VARCHAR(255) DEFAULT NULL,
                    thumbnail_path VARCHAR(255) DEFAULT NULL,
                    exif_removed TINYINT(1) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_filename (filename),
                    INDEX idx_favorite (favorite),
                    INDEX idx_super_promoted (super_promoted),
                    INDEX idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'users' => "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    email VARCHAR(255) DEFAULT NULL,
                    role ENUM('admin', 'user') DEFAULT 'user',
                    is_active TINYINT(1) NOT NULL DEFAULT 1,
                    last_login TIMESTAMP NULL,
                    login_attempts INT DEFAULT 0,
                    locked_until TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_username (username),
                    INDEX idx_role (role),
                    INDEX idx_is_active (is_active)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'sessions' => "
                CREATE TABLE IF NOT EXISTS sessions (
                    id VARCHAR(128) PRIMARY KEY,
                    user_id INT DEFAULT NULL,
                    ip_address VARCHAR(45) DEFAULT NULL,
                    user_agent TEXT DEFAULT NULL,
                    payload TEXT NOT NULL,
                    last_activity INT NOT NULL,
                    INDEX idx_user_id (user_id),
                    INDEX idx_last_activity (last_activity),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'rate_limits' => "
                CREATE TABLE IF NOT EXISTS rate_limits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    ip_address VARCHAR(45) NOT NULL,
                    endpoint VARCHAR(255) NOT NULL,
                    requests_count INT DEFAULT 1,
                    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_ip_endpoint (ip_address, endpoint),
                    INDEX idx_window_start (window_start)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'settings' => "
                CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    setting_key VARCHAR(100) NOT NULL UNIQUE,
                    setting_value TEXT DEFAULT NULL,
                    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
                    description TEXT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_setting_key (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            "
        ];
        
        foreach ($tables as $tableName => $sql) {
            try {
                $this->connection->exec($sql);
            } catch (PDOException $e) {
                error_log("Failed to create table {$tableName}: " . $e->getMessage());
            }
        }
        
        // Insert default admin user if not exists
        $this->createDefaultAdmin();
        
        // Insert default settings if not exists
        $this->createDefaultSettings();
    }
    
    /**
     * Create default admin user
     */
    private function createDefaultAdmin() {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $passwordHash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
            $stmt = $this->connection->prepare("
                INSERT INTO users (username, password_hash, role, is_active) 
                VALUES (?, ?, 'admin', 1)
            ");
            $stmt->execute([ADMIN_USERNAME, $passwordHash]);
        }
    }
    
    /**
     * Create default settings
     */
    private function createDefaultSettings() {
        $defaultSettings = [
            ['app_name', 'PhotoGallery CMS', 'string', 'Application name'],
            ['app_language', 'cs', 'string', 'Default language'],
            ['app_theme', 'auto', 'string', 'Default theme'],
            ['image_quality', '85', 'integer', 'Image quality for WebP conversion'],
            ['max_upload_size', '10485760', 'integer', 'Maximum upload size in bytes'],
            ['auto_convert_webp', '1', 'boolean', 'Automatically convert images to WebP'],
            ['remove_exif', '1', 'boolean', 'Remove EXIF data from images'],
            ['enable_rate_limiting', '1', 'boolean', 'Enable rate limiting'],
            ['rate_limit_requests', '100', 'integer', 'Maximum requests per window'],
            ['rate_limit_window', '3600', 'integer', 'Rate limit window in seconds']
        ];
        
        foreach ($defaultSettings as $setting) {
            $stmt = $this->connection->prepare("
                INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, description) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute($setting);
        }
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Fetch a single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert a record and return the last insert ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->connection->lastInsertId();
    }
    
    /**
     * Update a record
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setClause) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Delete a record
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $this->query($sql, [$tableName]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get table structure
     */
    public function getTableStructure($tableName) {
        $sql = "DESCRIBE {$tableName}";
        return $this->fetchAll($sql);
    }
    
    /**
     * Close database connection
     */
    public function close() {
        $this->connection = null;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}
