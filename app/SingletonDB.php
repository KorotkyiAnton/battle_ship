<?php

namespace app;

use PDO;
use PDOException;

class SingletonDB
{
    private static ?SingletonDB $instance = null;
    private PDO $pdo;

    private string $host = '127.0.0.1'; //10.10.1.133
    private string $db = 'study';
    private string $user = 'root'; //a.korotkyi
    private string $pass = ''; //HY&er98f
    private string $charset = 'utf8';

    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Handle database connection error here
            die("Database connection failed: " . $e->getMessage());
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