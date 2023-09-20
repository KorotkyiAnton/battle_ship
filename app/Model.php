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

    public function isLoginUnique($param): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT COUNT(LOWER(login)) 'login'  FROM Users  WHERE login = LOWER(?)");
        $statement->execute([$param]);
        return !($statement->fetchAll()[0]["login"]);
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
            "SELECT id, is_online FROM Users WHERE LOWER(Users.login) = LOWER(?)"
        );
        $statement->execute([$login]);
        $fetchData = $statement->fetchAll();
        $userId = $fetchData[0]["id"];
        $isOnline = $fetchData[0]["is_online"];
        $userStatus = 0;

        if(!is_null($userId) && !$isOnline) {
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

    public function updateUserStatusInQueues($login): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("UPDATE Queues SET status = 1 WHERE user_id = (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?))");
        return $statement->execute([$login]);
    }

    public function getUserIdWhereStatusInSearch($login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT id  FROM Queues  WHERE status = 1 AND 
                              user_id = (SELECT id FROM Users WHERE NOT LOWER(Users.login) = LOWER(?) LIMIT 1)");
        $statement->execute([$login]);
        return intval($statement->fetchAll()[0]["id"]);
    }

    public function createNewGameInGames($login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Games (first_player, first_turn) VALUES (?, 52)");
        return $statement->execute([$login]);
    }
}