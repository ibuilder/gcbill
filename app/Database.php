<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private ?PDO $connection = null; // Initialize as null
    private array $config;

    /**
     * Constructor expects the database-specific configuration array.
     * Example: ['driver' => 'mysql', 'host' => '...', ...]
     *
     * @param array $dbConfig Database configuration array.
     * @throws \InvalidArgumentException If required config keys are missing.
     * @throws \RuntimeException If connection fails.
     */
    public function __construct(array $dbConfig)
    {
        // Validate required keys
        $requiredKeys = ['driver', 'host', 'port', 'database', 'username', 'password', 'charset'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $dbConfig)) {
                throw new \InvalidArgumentException("Database configuration missing required key: '{$key}'.");
            }
        }

        $this->config = $dbConfig;
        $this->connect();
    }

    /**
     * Establishes the database connection.
     *
     * @throws \RuntimeException If connection fails.
     */
    private function connect(): void
    {
        // Construct DSN string
        $dsn = "{$this->config['driver']}:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";

        // Default PDO options from config or set sensible defaults
        $options = $this->config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
        } catch (PDOException $e) {
            // Log the error securely (avoid logging passwords if possible)
            error_log("Database Connection Error: " . $e->getMessage());
            // Throw a more generic exception to the caller
            throw new \RuntimeException("Could not connect to the database. Please check configuration and server status.", 0, $e);
        }
    }

    /**
     * Get the raw PDO connection instance.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        // Ensure connection is established
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Prepare and execute a SQL query.
     * Logs errors and returns false on failure.
     *
     * @param string $query SQL query string.
     * @param array $params Parameters to bind to the query.
     * @return \PDOStatement|false The PDOStatement on success, false on failure.
     */
    public function query(string $query, array $params = []): \PDOStatement|false
    {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Log the error with query details (consider security implications of logging params)
            error_log("Database Query Error: " . $e->getMessage() . " | Query: " . $query /* . " | Params: " . json_encode($params) */);
            return false; // Indicate failure
        }
    }

    /**
     * Execute a SELECT query and return all rows.
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array Result rows (empty array if no results or error)
     */
    public function select(string $query, array $params = []): array
    {
        $stmt = $this->query($query, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /**
     * Execute a SELECT query and return a single row.
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return array|null Result row or null if not found or error
     */
    public function selectOne(string $query, array $params = []): ?array
    {
        $stmt = $this->query($query, $params);
        if (!$stmt) return null;
        $result = $stmt->fetch();
        // fetch() returns false if no more rows, so check explicitly
        return $result !== false ? $result : null;
    }

    /**
     * Execute a SELECT query and return a single value from the first column.
     *
     * @param string $query SQL query
     * @param array $params Query parameters
     * @return mixed Result value or null if not found or error
     */
    public function selectValue(string $query, array $params = []): mixed
    {
        $stmt = $this->query($query, $params);
        if (!$stmt) return null;
        $result = $stmt->fetchColumn();
        // fetchColumn() returns false if no more rows
        return $result !== false ? $result : null;
    }

    /**
     * Execute an INSERT query.
     *
     * @param string $table Table name
     * @param array $data Data to insert (column => value)
     * @return string|false Last insert ID or false on failure
     */
    public function insert(string $table, array $data): string|false
    {
        if (empty($data)) {
            return false;
        }
        // Quote column names properly
        $columns = implode(', ', array_map(fn($col) => "`" . str_replace("`", "``", $col) . "`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $query = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->query($query, array_values($data));
        // Check if statement executed successfully before getting lastInsertId
        return $stmt ? $this->getConnection()->lastInsertId() : false;
    }

    /**
     * Execute an UPDATE query.
     *
     * @param string $table Table name
     * @param array $data Data to update (column => value)
     * @param string $where WHERE clause (e.g., "id = ? AND status = ?")
     * @param array $whereParams Parameters for the WHERE clause
     * @return int|false Number of affected rows, or false on error
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int|false
    {
        if (empty($data)) {
            return 0; // No data to update, 0 rows affected
        }
        $setParts = [];
        $values = [];
        foreach ($data as $column => $value) {
            // Quote column names properly
            $setParts[] = "`" . str_replace("`", "``", $column) . "` = ?";
            $values[] = $value;
        }
        $setClause = implode(', ', $setParts);
        // Basic validation/sanitization on $table and $where might be needed depending on source
        $query = "UPDATE `{$table}` SET {$setClause} WHERE {$where}";

        $params = array_merge($values, $whereParams);
        $stmt = $this->query($query, $params);

        // Return rowCount on success, false on failure
        return $stmt ? $stmt->rowCount() : false;
    }

    /**
     * Execute a DELETE query.
     *
     * @param string $table Table name
     * @param string $where WHERE clause
     * @param array $params WHERE parameters
     * @return int|false Number of affected rows, or false on error
     */
    public function delete(string $table, string $where, array $params = []): int|false
    {
        // Basic validation/sanitization on $table and $where might be needed
        $query = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->query($query, $params);
        // Return rowCount on success, false on failure
        return $stmt ? $stmt->rowCount() : false;
    }

    // --- Transaction Methods ---
    public function beginTransaction(): bool { return $this->getConnection()->beginTransaction(); }
    public function commit(): bool { return $this->getConnection()->commit(); }
    public function rollBack(): bool { return $this->getConnection()->rollBack(); } // Corrected method name casing

    // Destructor to close connection (optional, PHP usually handles this)
    public function __destruct() {
        $this->connection = null;
    }
}