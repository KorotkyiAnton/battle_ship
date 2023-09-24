<?php

namespace app;

use PDO;

require_once __DIR__ . "/SingletonDB.php";

class Model
{

    private ?SingletonDB $db;

    public function __construct()
    {
        // Получаем экземпляр SingletonDB
        $this->db = SingletonDB::getInstance();
    }

    public function getUserIdFromLogin($login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT id  FROM Users  WHERE LOWER(login) = LOWER(?)");
        $statement->execute([$login]);
        return intval($statement->fetchAll()[0]["id"]);
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
        $statement = $connection->prepare("UPDATE Queues SET status = ? WHERE user_id = ?");
        return $statement->execute([$status, $this->getUserIdFromLogin($login)]);
    }

    public function getUserIdWhereStatusInSearch($login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT user_id  FROM Queues  WHERE status = 1 AND NOT user_id = ?");
        $statement->execute([$this->getUserIdFromLogin($login)]);
        return intval($statement->fetchAll()[0]["user_id"]);
    }

    public function createNewGameInGames($login, $randNumber): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Games (first_player, first_player_roll) VALUES (?, ?)");
        $statement->execute([$this->getUserIdFromLogin($login), $randNumber]);

        return intval($connection->lastInsertId());
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

        $statement = $connection->prepare("UPDATE Games SET second_player = ?, second_player_roll = ? WHERE id = ?");
        $statement->execute([$this->getUserIdFromLogin($login), $randNumber, $gameId]);

        $statement = $connection->prepare(
            "SELECT first_player_roll FROM Games WHERE id = ?");
        $statement->execute([$gameId]);
        $first_turn = $statement->fetchAll()[0]["first_player_roll"];

        return [intval($gameId), intval($first_turn)];
    }

    public function getSecondPlayerRollFromGames(int $newGameId): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT second_player_roll FROM Games WHERE id = ?");
        $statement->execute([$newGameId]);
        $first_turn = $statement->fetchAll()[0]["second_player_roll"];

        return intval($first_turn);
    }

    public function deleteGameWithEmptySecondPlayerFromGames($login): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM Games WHERE first_player = ? AND second_player IS NULL");
        return $statement->execute([$this->getUserIdFromLogin($login)]);
    }

    public function addShipAndCoordinatesToPrivateTable($shipCoordinates, int $gameId): void
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM CoordinatesKorotkyi");
        $statement->execute([]);
        $statement = $connection->prepare("DELETE FROM ShipsKorotkyi");
        $statement->execute([]);

        foreach ($shipCoordinates as $shipName => $shipData) {
            // Добавляем информацию о корабле в таблицу ShipsKorotkyi
            $shipType = count($shipData['coords']); // Определяем тип корабля по количеству координат
            $direction = $shipData['orientation'];
            $is_destroyed = false; // При добавлении кораблей предполагаем, что они не разрушены
            $startCoordinate = $shipData['coords'][0];
            $statement = $connection->prepare("INSERT INTO ShipsKorotkyi (game_id, ship_type, direction, is_destroyed, start_coordinate) VALUES (?, ?, ?, ?, ?)");
            $statement->execute([$gameId, $shipType, $direction, $is_destroyed, $startCoordinate]);

            // Получаем ID вновь добавленного корабля
            $shipId = $connection->lastInsertId();

            // Добавляем информацию о координатах корабля в таблицу CoordinatesKorotkyi
            foreach ($shipData['coords'] as $coord) {
                $coordinate = $coord;
                $isHit = false; // При добавлении координат предполагаем, что не было попаданий

                $statement = $connection->prepare("INSERT INTO CoordinatesKorotkyi (ship_id, coordinate, is_hit) VALUES (?, ?, ?)");
                $statement->execute([$shipId, $coordinate, $isHit]);
            }
        }
    }

    public function deleteUserFromQueues($login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM Queues WHERE user_id = ?");
        $statement->execute([$this->getUserIdFromLogin($login)]);
    }

    public function deleteUserFromUsers($login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM Users WHERE LOWER(login) = LOWER(?)");
        $statement->execute([$login]);
    }

    public function getShipsFromDB(): array
    {
        $shipData = [];

        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT s.id AS ship_id, s.ship_type, s.direction, s.is_destroyed, s.start_coordinate,
                   c.coordinate, c.is_hit
            FROM ShipsKorotkyi s
            JOIN CoordinatesKorotkyi c ON s.id = c.ship_id");
        $statement->execute([]);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $shipId = $row["ship_id"];
            $shipType = $row["ship_type"];
            $direction = $row["direction"];
            $isDestroyed = (bool)$row["is_destroyed"];
            $startCoordinate = $row["start_coordinate"];
            $coordinate = $row["coordinate"];
            $isHit = (bool)$row["is_hit"];

            // Формирование ключа для корабля
            $shipKey = "ship" . $shipId;

            // Создание записи для корабля (если еще не существует)
            if (!isset($shipData[$shipKey])) {
                $shipData[$shipKey] = [
                    "coords" => [],
                    "hits" => 0,
                    "shipStart" => $startCoordinate,
                    "orientation" => $direction
                ];
            }

            // Добавление координаты в запись корабля
            $shipData[$shipKey]["coords"][] = $coordinate;

            // Увеличение счетчика попаданий, если есть попадание
            if ($isHit) {
                $shipData[$shipKey]["hits"]++;
            }
        }

        return $shipData;
    }

    public function getGameRecordFromGames($login): array
    {
        $userId = $this->getUserIdFromLogin($login);

        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT id, first_player, second_player, first_player_roll, second_player_roll FROM Games 
                                                   WHERE (first_player = ? OR second_player = ?) AND winner IS NULL ORDER BY id DESC LIMIT 1");
        $statement->execute([$userId, $userId]);
        $fetchData = $statement->fetchAll()[0];
        $secondPlayerLogin = intval($fetchData["first_player"]) === $userId ?
            $this->getSecondUserLoginFromUsers($fetchData["second_player"]) :
            $this->getSecondUserLoginFromUsers($fetchData["first_player"]);
        $firstTurn = intval($fetchData["first_player"]) === $userId ?
            intval($fetchData["first_player_roll"]) > intval($fetchData["second_player_roll"]) :
            intval($fetchData["second_player_roll"]) > intval($fetchData["first_player_roll"]);

        return [$fetchData["id"], $userId, $secondPlayerLogin, $firstTurn];
    }

    public function countTurns($gameId): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT COUNT(id)'turn_number' FROM Shots WHERE game_id = ? AND response IS NULL");
        $statement->execute([$gameId]);
        $turn = $statement->fetchAll()[0]["turn_number"];

        return intval($turn);
    }

    public function sendRequestToShots($gameId, $shotCoords, $login): void
    {
        $userId = $this->getUserIdFromLogin($login);

        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Shots (player_id, game_id, target, request, response, turn_number, shot_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->execute([$userId, $gameId, $shotCoords, 1, NULL, $this->countTurns($gameId)+1, date('Y-m-d H:i:s')]);
    }

    public function getResponseStatusFromShots($gameId, $shotCoords, $login): ?int
    {
        $userId = $this->getUserIdFromLogin($login);

        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT response FROM Shots WHERE game_id = ? AND target = ? AND NOT player_id = ? AND turn_number = ?");
        $statement->execute([$gameId, $shotCoords, $userId, $this->countTurns($gameId)]);

        return $statement->fetchAll()[0]["response"];
    }

    public function getUserOnlineStatusFromUsers(int $opponentId): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT is_online FROM Users WHERE id = ?");
        $statement->execute([$opponentId]);

        return boolval($statement->fetchAll()[0]["is_online"]);
    }

    public function getRequestFromShots($gameId, $login): string
    {
        $userId = $this->getUserIdFromLogin($login);

        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT target FROM Shots WHERE game_id = ? AND turn_number = ? ORDER BY id DESC LIMIT 1");
        $statement->execute([$gameId, $this->countTurns($gameId)]);
        $target = $statement->fetchAll()[0]["target"];
        echo $target."; ".$this->countTurns($gameId);

        $statement = $connection->prepare("UPDATE CoordinatesKorotkyi SET is_hit = true WHERE coordinate = ?");
        $statement->execute([$target]);

        return strval($target);
    }

    public function checkIfOpponentHit(string $target, $gameId, $login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT ship_id FROM CoordinatesKorotkyi WHERE coordinate = ?");
        $statement->execute([$target]);
        $hitShipId = $statement->fetchAll()[0]["ship_id"];
        $outputArray = [];
        $userId = $this->getUserIdFromLogin($login);

        if ($hitShipId === null) {
            $this->insertResponseToShots($userId, $gameId, $target, 0);
            return 0;
        }
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT coordinate, is_hit FROM CoordinatesKorotkyi WHERE ship_id = ?");
        $statement->execute([$hitShipId]);

        foreach ($statement->fetchAll() as $item) {
            $outputArray[$item['coordinate']] = intval($item['is_hit']);
        }

        $isHitCount = 0; // Счетчик попаданий
        //var_dump($outputArray);

        foreach ($outputArray as $isHit) {
            $isHit = (bool)$isHit;
            if ($isHit) {
                $isHitCount+=1;
            }
        }

        //echo $isHitCount . " " . count($outputArray);
        if ($isHitCount === count($outputArray)) {
            $connection = $this->db->getConnection();
            $statement = $connection->prepare("UPDATE ShipsKorotkyi SET is_destroyed = true WHERE id = ?");
            $statement->execute([$hitShipId]);
            $statement = $connection->prepare("SELECT direction FROM ShipsKorotkyi WHERE id = ?");
            $statement->execute([$hitShipId]);
            $direction = $statement->fetchAll()[0]["direction"];
            if ($direction === "right") {
                $this->insertResponseToShots($userId, $gameId, $target, 21, array_keys($outputArray)[count($outputArray) - 1]);
                return 21;
            } else if ($direction === "down") {
                $this->insertResponseToShots($userId, $gameId, $target, 22, array_keys($outputArray)[count($outputArray) - 1]);
                return 22;
            } else if ($direction === "left") {
                $this->insertResponseToShots($userId, $gameId, $target, 23, array_keys($outputArray)[0]);
                return 23;
            } else if ($direction === "up") {
                $this->insertResponseToShots($userId, $gameId, $target, 24, array_keys($outputArray)[0]);
                return 24;
            }
        }

        $this->insertResponseToShots($userId, $gameId, $target, 1);
        return 1;
    }

    private function insertResponseToShots(int $userId, $gameId, string $target, int $response, $startCoord = NULL): void
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Shots (player_id, game_id, target, request, response, turn_number, shot_time, start_coord) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute([$userId, $gameId, $target, 0, $response, $this->countTurns($gameId), date('Y-m-d H:i:s'), $startCoord]);
    }
}