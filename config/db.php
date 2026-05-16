<?php
declare(strict_types=1);
/**
 * Elite School Management - Database Configuration & PDO Singleton
 * Provides secure, centralized database connection with proper error handling
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'elite_school_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    
    private function __construct() {}
    private function __clone() {}
    
    /**
     * Get PDO singleton instance
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", DB_HOST, DB_NAME, DB_CHARSET);
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_FOUND_ROWS   => true,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]);
            } catch (PDOException $e) {
                error_log(date('Y-m-d H:i:s') . ' DB Connection Error: ' . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../logs/db.log');
                http_response_code(503);
                die('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Database Error</title></head><body style="font-family:sans-serif;text-align:center;padding:50px"><h2>🔧 সার্ভার রক্ষণাবেক্ষণ</h2><p>Database connection failed. Please contact administrator.</p></body></html>');
            }
        }
        return self::$instance;
    }
    
    /**
     * Execute prepared statement
     * @param string $sql SQL query with placeholders
     * @param array $params Parameter values
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch single row
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|null
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        return self::query($sql, $params)->fetch() ?: null;
    }
    
    /**
     * Fetch all rows
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }
    
    /**
     * Insert data and return last insert ID
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int Last insert ID
     */
    public static function insert(string $table, array $data): int {
        $cols = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO `$table` ($cols) VALUES ($placeholders)", array_values($data));
        return (int)self::getInstance()->lastInsertId();
    }
    
    /**
     * Update data and return affected rows
     * @param string $table Table name
     * @param array $data Data to update
     * @param string $where WHERE clause (e.g., "id = ?")
     * @param array $whereParams WHERE parameters
     * @return int Affected rows
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int {
        $sets = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));
        $stmt = self::query("UPDATE `$table` SET $sets WHERE $where", array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }
    
    /**
     * Delete data and return affected rows
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $whereParams WHERE parameters
     * @return int Affected rows
     */
    public static function delete(string $table, string $where, array $whereParams = []): int {
        $stmt = self::query("DELETE FROM `$table` WHERE $where", $whereParams);
        return $stmt->rowCount();
    }
    
    // Transaction methods
    public static function beginTransaction(): void { self::getInstance()->beginTransaction(); }
    public static function commit(): void { self::getInstance()->commit(); }
    public static function rollback(): void { self::getInstance()->rollBack(); }
    public static function inTransaction(): bool { return self::getInstance()->inTransaction(); }
}
