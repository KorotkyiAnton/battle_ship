<?php

namespace app;

use PDO;
use PDOException;

class SingletonDB
{
    private static ?SingletonDB $instance = null;
    private PDO $pdo;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $charset = $_ENV['DB_CHARSET'];

        try {
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
            $this->pdo = new PDO($dsn, $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Handle database connection error here
            die(json_encode("Database connection failed: " . $e->getMessage()));
        }
    }

    // Get the singleton instance of the Database class
    public static function getInstance(): ?SingletonDB
    {
        if (!self::$instance) {
            self::$instance = new SingletonDB();
        }
        return self::$instance;
    }

    // Get the PDO connection
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // Prevent cloning of the object
    private function __clone() {}

    // Prevent serialization of the object
    public function __wakeup() {}
}