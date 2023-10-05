<?php

namespace app;

use Exception;
use PDO;
use PDOException;

class SingletonDB
{
    private static ?SingletonDB $instance = null;
    private PDO $pdo;

    /**
     * Приватный конструктор, чтобы предотвратить прямое создание экземпляра
     */
    private function __construct() {
        // Получаем конфигурацию базы данных из переменных окружения
        $host = $_ENV['DB_HOST'];
        $db = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];
        $charset = $_ENV['DB_CHARSET'];

        try {
            // Создаем строку DSN для подключения PDO
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

            // Создаем новый экземпляр PDO с информацией о подключении к базе данных
            $this->pdo = new PDO($dsn, $user, $pass);

            // Устанавливаем режим обработки ошибок PDO на выброс исключений
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Обрабатываем ошибку подключения к базе данных здесь
            die(json_encode("Ошибка подключения к базе данных: " . $e->getMessage()));
        }
    }

    /**
     * Получить единственный экземпляр класса Database.
     *
     * @return SingletonDB|null Единственный экземпляр класса Database.
     */
    public static function getInstance(): ?SingletonDB
    {
        // Проверяем, что экземпляр еще не создан
        if (!self::$instance) {
            // Создаем новый экземпляр, если он еще не существует
            self::$instance = new SingletonDB();
        }
        // Возвращаем единственный экземпляр
        return self::$instance;
    }

    /**
     * Получить соединение PDO.
     *
     * @return PDO Соединение PDO.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Запрещает клонирование объекта.
     *
     * @throws Exception При попытке клонирования.
     */
    private function __clone()
    {
        throw new Exception("Клонирование запрещено.");
    }

    /**
     * Запрещает сериализацию объекта.
     *
     * @return void
     */
    public function __wakeup()
    {
        // Не требуется дополнительных действий для предотвращения сериализации.
    }
}