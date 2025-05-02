php
<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private $pdo;
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        try {
            $dsn = sprintf("%s:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}