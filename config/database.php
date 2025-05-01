<?php

namespace App; // Add namespace

use PDO;
use PDOException;

/**
 * Database Connection Class (Singleton)
 */
class Database {
    private static ?Database $instance = null; // Singleton instance
    private PDO $connection; // PDO connection object

    private string $host;
    private string $username;
    private string $password;
    private string $database;
    private string $charset;
    private string $port;
    private string $driver;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {
        // Access config globally (consider dependency injection later)
        global $config;
        if (!isset($config['db'])) {
            throw new \Exception("Database configuration not found.");
        }
        $dbConfig = $config['db'];

        $this->host = $dbConfig['host'];
        $this->username = $dbConfig['username'];
        $this->password = $dbConfig['password'];
        $this->database = $dbConfig['database'];
        $this->charset = $dbConfig['charset'];
        $this->port = $dbConfig['port'];
        $this->driver = $dbConfig['driver'];

        $this->connect();
    }

    /**
     * Get the singleton database instance.
     * @return Database
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish the database connection.
     */
    private function connect(): void {
        $dsn = "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log the error securely, don't expose details in production
            error_log("Database Connection Error: " . $e->getMessage());
            // Provide a user-friendly error message or re-throw a custom exception
            throw new \RuntimeException("Could not connect to the database. Please check configuration.", 0, $e);
        }
    }

    /**
     * Get the raw PDO connection object.
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Prepare and execute a query, returning the PDOStatement.
     * Handles potential exceptions during execution.
     *
     * @param string $query SQL query string with placeholders (e.g., ?, :name).
     * @param array $params Parameters to bind to the query.
     * @return \PDOStatement|false The PDOStatement object on success, or false on failure.
     */
    public function query(string $query, array $params = []): \PDOStatement|false
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Log the error including the query (be careful with sensitive data in logs)
            error_log("Database Query Error: " . $e->getMessage() . " | Query: " . $query . " | Params: " . json_encode($params));
            // Optionally re-throw or return false depending on desired error handling
            // For simplicity here, we return false. Consider throwing a custom DB exception.
            return false;
        }
    }

    /**
     * Execute a SELECT query and return all rows.
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Result rows (empty array if no results or error)
     */
    public function select(string $query, array $params = []): array {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Execute a SELECT query and return a single row.
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|null Result row or null if not found or error
     */
    public function selectOne(string $query, array $params = []): ?array {
        $stmt = $this->query($query, $params);
        if (!$stmt) return null;
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Execute a SELECT query and return a single value from the first column.
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return mixed Result value or null if not found or error
     */
    public function selectValue(string $query, array $params = []): mixed {
        $stmt = $this->query($query, $params);
         if (!$stmt) return null;
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }

    /**
     * Execute an INSERT query.
     * @param string $table Table name
     * @param array $data Data to insert (column => value)
     * @return string|false Last insert ID or false on failure
     */
    public function insert(string $table, array $data): string|false {
        if (empty($data)) {
            return false;
        }
        $columns = implode(', ', array_map(fn($col) => "`$col`", array_keys($data))); // Quote column names
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->query($query, array_values($data));
        return $stmt ? $this->connection->lastInsertId() : false;
    }

    /**
     * Execute an UPDATE query.
     * @param string $table Table name
     * @param array $data Data to update (column => value)
     * @param string $where WHERE clause (e.g., "id = ? AND status = ?")
     * @param array $whereParams Parameters for the WHERE clause
     * @return int Number of affected rows, or -1 on error
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
         if (empty($data)) {
            return 0;
        }
        $setParts = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?"; // Quote column names
            $values[] = $value;
        }
        $setClause = implode(', ', $setParts);
        $query = "UPDATE `{$table}` SET {$setClause} WHERE {$where}";

        $params = array_merge($values, $whereParams);
        $stmt = $this->query($query, $params);

        return $stmt ? $stmt->rowCount() : -1; // Return -1 or throw exception on error
    }

    /**
     * Execute a DELETE query.
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int Number of affected rows, or -1 on error
     */
    public function delete(string $table, string $where, array $params = []): int {
        $query = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->rowCount() : -1; // Return -1 or throw exception on error
    }

    // --- Transaction Methods ---
    public function beginTransaction(): bool { return $this->connection->beginTransaction(); }
    public function commit(): bool { return $this->connection->commit(); }
    public function rollback(): bool { return $this->connection->rollBack(); }

    /**
     * Prevent cloning of the instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the instance.
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}