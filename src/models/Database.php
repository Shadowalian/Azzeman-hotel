<?php
/**
 * Database Model - PDO Wrapper
 * 
 * Provides a secure database connection using PDO with prepared statements.
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        // Check if PDO extension is loaded
        if (!extension_loaded('pdo')) {
            $error = 'PDO extension is not installed. Please enable the PDO extension in your PHP configuration.';
            error_log('Database error: ' . $error);
            throw new Exception($error);
        }
        
        // Check if PDO MySQL driver is available
        if (!in_array('mysql', PDO::getAvailableDrivers())) {
            $error = 'PDO MySQL driver is not installed. Please enable the pdo_mysql extension in your PHP configuration.';
            error_log('Database error: ' . $error);
            error_log('Available PDO drivers: ' . implode(', ', PDO::getAvailableDrivers()));
            throw new Exception($error);
        }
        
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            error_log('Database connection failed: ' . $errorMsg);
            
            // Provide more helpful error messages
            if (strpos($errorMsg, 'could not find driver') !== false) {
                $error = 'PDO MySQL driver is not installed. Please enable the pdo_mysql extension in your PHP configuration.';
            } elseif (strpos($errorMsg, 'Access denied') !== false) {
                $error = 'Database access denied. Please check your database username and password in config.php';
            } elseif (strpos($errorMsg, 'Unknown database') !== false) {
                $error = 'Database not found. Please check your database name in config.php';
            } elseif (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'Host') !== false) {
                $error = 'Cannot connect to database server. Please check your database host in config.php';
            } else {
                $error = 'Database connection failed: ' . $errorMsg;
            }
            
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new Exception($error);
            }
            throw new Exception('Database connection failed. Please check your configuration.');
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute a SELECT query and return all results
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database query failed.');
        }
    }
    
    /**
     * Execute a SELECT query and return a single row
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database query failed.');
        }
    }
    
    /**
     * Execute an INSERT, UPDATE, or DELETE query
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Execute failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw new Exception('Database operation failed.');
        }
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
}

