<?php

namespace app;
require_once __DIR__ . "/SingletonDB.php";

class Model
{

    private ?SingletonDB $db;

    public function __construct()
    {
        // Получаем экземпляр SingletonDB
        $this->db = SingletonDB::getInstance();
    }

    public function createRecord($tableName, $data)
    {
        // Реализация создания записи в таблице
        // $tableName - имя таблицы
        // $data - ассоциативный массив с данными для вставки
        // Возвращает true в случае успеха или false в случае ошибки
    }

    public function isLoginUnique($param): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT COUNT(LOWER(login)) 'login'  FROM Users  WHERE login = LOWER(?)");
        $statement->execute([$param]);
        return !($statement->fetchAll()[0]["login"]);
    }

    public function updateRecord($tableName, $id, $data)
    {
        // Реализация обновления записи в таблице по ID
        // $tableName - имя таблицы
        // $id - идентификатор записи
        // $data - ассоциативный массив с данными для обновления
        // Возвращает true в случае успеха или false в случае ошибки
    }

    public function deleteRecord($tableName, $id)
    {
        // Реализация удаления записи из таблицы по ID
        // $tableName - имя таблицы
        // $id - идентификатор записи
        // Возвращает true в случае успеха или false в случае ошибки
    }

    public function addLoginToDB($login): bool
    {
        $isOnline = true;
        $lastUpdate = new \DateTime();
        $lastUpdate = $lastUpdate->format('Y-m-d H:i:s');

        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Users (login, is_online, last_update) VALUES (?, ?, ?)");
        return $statement->execute([$login, $isOnline, $lastUpdate]);
    }

    public function getUserStatusFromQueues($login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?)"
        );
        $statement->execute([$login]);
        $userId = $statement->fetchAll()[0]["id"];
        $userStatus = "";

        if(!is_null($userId)) {
            $statement = $connection->prepare(
                "SELECT status FROM Queues WHERE user_id = ?"
            );
            $statement->execute([$userId]);
            $userStatus = $statement->fetchAll()[0]["status"];
            if (is_null($userStatus)) {
                $statement = $connection->prepare("INSERT INTO Queues (user_id, status) VALUES (?, ?)");
                $statement->execute([$userId, 0]);
            }
        }

        return intval($userStatus);
    }
}