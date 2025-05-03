<?php

namespace App;

class Database extends \PDO
{
    protected \PDO $pdo;

    public function __construct(array $config)
    {
        $dsn = $config['dsn'] . ';dbname=' . $config['name'];
        $username = $config['username'];
        $password = $config['password'];

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            parent::__construct($dsn, $username, $password, $options);
            $this->pdo = $this;
        } catch (\PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash_' . $type] = $message;
    }

    public function redirect(string $url): void
    {
        header("Location: " . $url, true, 302);
        exit;
    }
}