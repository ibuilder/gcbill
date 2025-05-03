<?php

namespace App\Models;

use App\Database;
use PDO;
use PDOException;

abstract class Model
{
    protected Database $db;
    protected static string $tableName; // Must be defined in child class
    protected static string $primaryKey = 'id'; // Default primary key

    // Model attributes (data)
    protected array $attributes = [];

    public function __construct(Database $db, array $attributes = [])
    {
        $this->db = $db;
        $this->fill($attributes);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Get all attributes.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Save the model to the database (inserts if new, updates if exists).
     *
     * @return bool True on success, false on failure.
     */
    public function save(): bool
    {
        $pk = static::$primaryKey;
        if (isset($this->attributes[$pk]) && !empty($this->attributes[$pk])) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert the model into the database.
     *
     * @return bool True on success, false on failure.
     */
    protected function insert(): bool
    {
        $data = $this->attributes;
        unset($data[static::$primaryKey]); // Don't include PK in insert data if auto-increment

        if (empty($data)) {
            return false;
        }

        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        $sql = "INSERT INTO " . static::$tableName . " ({$fields}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($data);
            if ($success) {
                // Set the ID if it's auto-incrementing
                $id = $this->db->lastInsertId();
                if ($id) {
                    $this->setAttribute(static::$primaryKey, (int)$id); // Cast to int
                }
            }
            return $success;
        } catch (PDOException $e) {
            error_log("Database Insert Error (" . static::$tableName . "): " . $e->getMessage());
            $this->db->setFlashMessage('error', 'A database error occurred. Please try again.');
            $this->db->redirect('/dashboard');
            return false;
        }
    }

    /**
     * Update the model in the database.
     *
     * @return bool True on success, false on failure.
     */
    protected function update(): bool
    {
        $pk = static::$primaryKey;
        $id = $this->attributes[$pk] ?? null;

        if (!$id) {
            return false; // Cannot update without a primary key value
        }

        $data = $this->attributes;
        unset($data[$pk]); // Don't include PK in update data

        if (empty($data)) {
            return true; // Nothing to update
        }

        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        $sql = "UPDATE " . static::$tableName . " SET {$setClause} WHERE {$pk} = :{$pk}";

        // Add primary key back to data for binding
        $data[$pk] = $id;

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Database Update Error (" . static::$tableName . "): " . $e->getMessage());
            $this->db->setFlashMessage('error', 'A database error occurred. Please try again.');
            $this->db->redirect('/dashboard');
            return false;
        }
    }

    /**
     * Delete the model from the database.
     *
     * @return bool True on success, false on failure.
     */
    public function delete(): bool
    {
        $pk = static::$primaryKey;
        $id = $this->attributes[$pk] ?? null;

        if (!$id) {
            return false; // Cannot delete without a primary key value
        }

        $sql = "DELETE FROM " . static::$tableName . " WHERE {$pk} = :{$pk}";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$pk => $id]);
        } catch (PDOException $e) {
            error_log("Database Delete Error (" . static::$tableName . "): " . $e->getMessage());
            $this->db->setFlashMessage('error', 'A database error occurred. Please try again.');
            $this->db->redirect('/dashboard');
            return false;
        }
    }

    /**
     * Find a model by its primary key.
     *
     * @param int|string $id
     * @param Database $db
     * @return static|null
     */
    public static function find(int|string $id, Database $db): ?static
    {
        $sql = "SELECT * FROM " . static::$tableName . " WHERE " . static::$primaryKey . " = ?";
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? new static($db, $data) : null;
        } catch (PDOException $e) {
            error_log("Database Find Error (" . static::$tableName . "): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Start building a query.
     *
     * @param Database $db
     * @return QueryBuilder
     */
    public static function query(Database $db): QueryBuilder
    {
        // Requires a QueryBuilder class to handle fluent queries
        return new QueryBuilder($db, static::class, static::$tableName);
    }

    /**
     * Basic 'where' clause (requires QueryBuilder).
     *
     * @param string $column
     * @param mixed $value
     * @param Database $db
     * @param string $operator
     * @return QueryBuilder
     */
    public static function where(string $column, mixed $value, Database $db, string $operator = '='): QueryBuilder
    {
        return static::query($db)->where($column, $operator, $value);
    }

     /**
     * Get all records for the model.
     *
     * @param Database $db
     * @return array Array of model instances.
     */
    public static function all(Database $db): array
    {
        return static::query($db)->get();
    }

}

// Basic Query Builder (can be expanded significantly or use a library)
class QueryBuilder
{
    protected Database $db;
    protected string $modelClass;
    protected string $tableName;
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $selectColumns = ['*'];

    public function __construct(Database $db, string $modelClass, string $tableName)
    {
        $this->db = $db;
        $this->modelClass = $modelClass;
        $this->tableName = $tableName;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $placeholder = ":where_" . count($this->bindings);
        $this->wheres[] = "{$column} {$operator} {$placeholder}";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

     public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function select(array $columns): self
    {
        $this->selectColumns = $columns;
        return $this;
    }

    public function get(array $columns = null): array
    {
        if ($columns !== null) {
            $this->select($columns);
        }
        $sql = $this->buildSelectQuery();
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->bindings);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $models = [];
            foreach ($results as $row) {
                $models[] = new $this->modelClass($this->db, $row);
            }
            return $models;
        } catch (PDOException $e) {
            error_log("Database Query Error ({$this->tableName}): " . $e->getMessage());
            return [];
        }
    }

    public function first(array $columns = null): ?object // Change return type to object
    {
         if ($columns !== null) {
            $this->select($columns);
        }
        $this->limit(1);
        $sql = $this->buildSelectQuery();
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($this->bindings);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
           
            return $data ? new $this->modelClass($this->db, $data) : null;
        } catch (PDOException $e) {
            error_log("Database Query Error ({$this->tableName}): " . $e->getMessage());
            return null;
        }
    }

    protected function buildSelectQuery(): string
    {
        $select = implode(', ', $this->selectColumns);
        $sql = "SELECT {$select} FROM {$this->tableName}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if (!empty($this->orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT " . $this->limit;
        }

        if ($this->offset !== null) {
             $sql .= " OFFSET " . $this->offset;
        }

        return $sql;
    }
}