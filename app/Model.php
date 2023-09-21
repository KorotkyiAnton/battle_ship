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

        if (!is_null($userId) && !$isOnline) {
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

    public function updateUserStatusInQueues($login, $status): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("UPDATE Queues SET status = ? WHERE user_id = (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?))");
        return $statement->execute([$status, $login]);
    }

    public function getUserIdWhereStatusInSearch($login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT user_id  FROM Queues  WHERE status = 1 AND 
                              NOT user_id = (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?))");
        $statement->execute([$login]);
        return intval($statement->fetchAll()[0]["user_id"]);
    }

    public function createNewGameInGames($login, $randNumber): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Games (first_player, first_turn) VALUES ((SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?)), ?)");
        $statement->execute([$login, $randNumber]);

        $statement = $connection->prepare(
            "SELECT id, first_turn  FROM Games  WHERE 
                              first_player = (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?)) AND
                              id = ?");
        $statement->execute([$login, $connection->lastInsertId()]);
        $fetchVal = $statement->fetchAll();
        return intval($fetchVal[0]["id"]);
    }

    public function getSecondUserLoginFromUsers(int $userIdInSearch): string
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT login FROM Users WHERE id = ?");
        $statement->execute([$userIdInSearch]);
        return strval($statement->fetchAll()[0]["login"]);
    }

    public function connectToCurrentGame($login, $first_player, $randNumber): array
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT id FROM Games WHERE 
                         first_player = ? 
                         ORDER BY id DESC LIMIT 1");
        $statement->execute([$first_player]);
        $gameId = $statement->fetchAll()[0]["id"];

        $statement = $connection->prepare("UPDATE Games SET second_player = (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?)), 
                 first_turn = CASE WHEN first_turn < ? THEN ? ELSE first_turn END");
        $statement->execute([$login, $randNumber, $randNumber]);

        $statement = $connection->prepare(
            "SELECT first_turn FROM Games WHERE id = ?");
        $statement->execute([$gameId]);
        $first_turn = $statement->fetchAll()[0]["first_turn"];

        return [intval($gameId), intval($first_turn)];
    }

    public function getFirstTurnFromGames(int $newGameId): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT first_turn FROM Games WHERE id = ?");
        $statement->execute([$newGameId]);
        $first_turn = $statement->fetchAll()[0]["first_turn"];

        return intval($first_turn);
    }

    public function deleteGameWithEmptySecondPlayerFromGames($login): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM Games WHERE first_player = 
                        (SELECT id FROM Users WHERE LOWER(Users.login) = LOWER(?)) AND second_player IS NULL");
        return $statement->execute([$login]);
    }
}