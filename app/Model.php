<?php

namespace app;

use Elastic\Apm\SpanContextHttpInterface;
use Exception;
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
        $fetchData = $statement->fetchAll()[0];
        return !($fetchData["login"]);
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

        if (!is_null($userId) || !$isOnline) {
            $statement = $connection->prepare(
                "SELECT status FROM Queues WHERE user_id = ?"
            );
            $statement->execute([$userId]);
            $userStatus = $statement->fetchAll()[0]["status"];
        }

        if (is_null($userStatus)) {
            $statement = $connection->prepare("INSERT INTO Queues (user_id, status) VALUES (?, ?)");
            $statement->execute([$userId, 0]);
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

    public function checkUserIdInGames($gameId): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT second_player  FROM Games  WHERE id = ?");
        $statement->execute([$gameId]);
        return intval($statement->fetchAll()[0]["second_player"]);
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

    public function addShipAndCoordinatesToPrivateTable($shipCoordinates, int $gameId, $login): void
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("DELETE FROM CoordinatesKorotkyi WHERE ship_id IN (SELECT id FROM ShipsKorotkyi WHERE user_id = ?)");
        $statement->execute([$this->getUserIdFromLogin($login)]);
        $statement = $connection->prepare("DELETE FROM ShipsKorotkyi WHERE user_id = ?");
        $statement->execute([$this->getUserIdFromLogin($login)]);

        foreach ($shipCoordinates as $shipName => $shipData) {
            // Добавляем информацию о корабле в таблицу ShipsKorotkyi
            $shipType = count($shipData['coords']); // Определяем тип корабля по количеству координат
            $direction = $shipData['orientation'];
            $is_destroyed = false; // При добавлении кораблей предполагаем, что они не разрушены
            if ($direction === "right" || $direction === "down") {
                $startCoordinate = $shipData['coords'][$shipType - 1];
            } else {
                $startCoordinate = $shipData["shipStart"];
            }
            $statement = $connection->prepare("INSERT INTO ShipsKorotkyi (game_id, ship_type, direction, is_destroyed, start_coordinate, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $statement->execute([$gameId, $shipType, $direction, $is_destroyed, $startCoordinate, $this->getUserIdFromLogin($login)]);

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
        $statement = $connection->prepare("UPDATE Users SET is_online=0 WHERE LOWER(login) = LOWER(?)");
        $statement->execute([$login]);
        $statement = $connection->prepare("DELETE FROM Users WHERE LOWER(login) = LOWER(?)");
        $statement->execute([$login]);
    }

    public function getShipsFromDB($login): array
    {
        $shipData = [];

        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT s.id AS ship_id, s.ship_type, s.direction, s.is_destroyed, s.start_coordinate,
                   c.coordinate, c.is_hit
            FROM ShipsKorotkyi s
            JOIN CoordinatesKorotkyi c ON s.id = c.ship_id WHERE user_id=?");
        $statement->execute([$this->getUserIdFromLogin($login)]);

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

            if ($direction === "right" || $direction === "down") {
                $shipData[$shipKey]["shipStart"] = $shipData[$shipKey]["coords"][count($coordinate) - 1];
            }

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
        if (!is_null($fetchData["first_player"])) {
            $secondPlayerLogin = intval($fetchData["first_player"]) === $userId ?
                $this->getSecondUserLoginFromUsers($fetchData["second_player"]) :
                $this->getSecondUserLoginFromUsers($fetchData["first_player"]);
            $firstTurn = intval($fetchData["first_player"]) === $userId ?
                intval($fetchData["first_player_roll"]) > intval($fetchData["second_player_roll"]) :
                intval($fetchData["second_player_roll"]) > intval($fetchData["first_player_roll"]);
        }


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
        $statement->execute([$userId, $gameId, $shotCoords, 1, NULL, $this->countTurns($gameId) + 1, date('Y-m-d H:i:s')]);
    }

    public function getResponseStatusFromShots($gameId, $shotCoords, $login): ?array
    {
        $userId = $this->getUserIdFromLogin($login);

        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT response, start_coord, ship_length FROM Shots WHERE game_id = ? AND target = ? AND NOT player_id = ? AND turn_number = ?");
        $statement->execute([$gameId, $shotCoords, $userId, $this->countTurns($gameId)]);
        $fetchedData = $statement->fetchAll()[0];
        $startCoord = $fetchedData["start_coord"];
        $length = intval($fetchedData["ship_length"]);
        $ships = null;

        if (!is_null($startCoord)) {
            $ships = $this->generateShipCoordinates($startCoord, intval($fetchedData["response"]) % 20, $length);
        }

        sort($ships);

        if (preg_match('/^\w{1}10$/', $ships[0]) === 1 && preg_match('/^\w{1}10$/', $ships[1]) !== 1) {
            $firstCoordinate = array_shift($ships);
            $ships[] = $firstCoordinate;
        }

        return [$fetchedData["response"], $ships];
    }

    public function generateShipCoordinates($startCoord, $direction, $length): array
    {
        $shipCoordinates = [$startCoord]; // Начальная координата

        // Определите смещения для каждого направления
        $dx = 0;
        $dy = 0;

        if ($direction == 1) {
            $dx = -1; // Вправо
        } elseif ($direction == 2) {
            $dy = -1; // Вниз
        } elseif ($direction == 3) {
            $dx = 1; // Влево
        } elseif ($direction == 4) {
            $dy = 1; // Вверх
        }

        // Добавляем остальные координаты корабля
        for ($i = 1; $i < $length; $i++) {
            $x = ord($startCoord[0]) + $i * $dx;
            $y = intval(substr($startCoord, 1)) + $i * $dy;
            $newCoord = chr($x) . $y;
            $shipCoordinates[] = $newCoord;
        }

        return $shipCoordinates;
    }

    /**
     * @throws Exception
     */
    public function getUserOnlineStatusFromUsers(int $opponentId): bool
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT last_update FROM Users WHERE id = ?");
        $statement->execute([$opponentId]);

        $currentDateTime = new \DateTime();

        $tableDateTime = new \DateTime(strval($statement->fetchAll()[0]["last_update"]));

        return abs($currentDateTime->getTimestamp() - $tableDateTime->getTimestamp());
    }

    public function checkResponse(int $gameId): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT turn_number  'turn_number' FROM Shots WHERE response IS NOT NULL AND game_id = ? ORDER BY id DESC LIMIT 1");
        $statement->execute([$gameId]);

        return intval($statement->fetchAll()[0]["turn_number"]) + 1;
    }

    public function getRequestFromShots($gameId, $login): string
    {
        $query = "SELECT target FROM Shots WHERE game_id = ? AND turn_number = ? AND NOT player_id = ? ORDER BY id DESC LIMIT 1";

        $connection = $this->db->getConnection();
        $statement = $connection->prepare($query);
        $statement->execute([$gameId, $this->checkResponse($gameId), $this->getUserIdFromLogin($login)]);
        $target = $statement->fetchAll()[0]["target"];

        $statement = $connection->prepare("UPDATE CoordinatesKorotkyi SET is_hit = true WHERE coordinate = ?");
        $statement->execute([$target]);

        return strval($target);
    }

    public function checkIfOpponentHit(string $target, $gameId, $login): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT DISTINCT ship_id FROM CoordinatesKorotkyi WHERE coordinate = ? AND ship_id IN 
                                                                 (SELECT id FROM ShipsKorotkyi WHERE user_id = ? AND game_id=?)");
        $statement->execute([$target, $this->getUserIdFromLogin($login), $gameId]);
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

        $isHitCount = 0;

        foreach ($outputArray as $isHit) {
            $isHit = (bool)$isHit;
            if ($isHit) {
                $isHitCount += 1;
            }
        }

        if ($isHitCount === count($outputArray)) {
            $connection = $this->db->getConnection();
            $statement = $connection->prepare("UPDATE ShipsKorotkyi SET is_destroyed = true WHERE id = ?");
            $statement->execute([$hitShipId]);
            $statement = $connection->prepare("SELECT direction, ship_type FROM ShipsKorotkyi WHERE id = ?");
            $statement->execute([$hitShipId]);
            $fetchData = $statement->fetchAll()[0];
            $direction = $fetchData["direction"];
            $length = $fetchData["ship_type"];
            if ($direction === "right") {
                $this->insertResponseToShots($userId, $gameId, $target, 21, array_keys($outputArray)[count($outputArray) - 1], $length);
                return 21;
            } else if ($direction === "down") {
                $this->insertResponseToShots($userId, $gameId, $target, 22, array_keys($outputArray)[count($outputArray) - 1], $length);
                return 22;
            } else if ($direction === "left") {
                $this->insertResponseToShots($userId, $gameId, $target, 23, array_keys($outputArray)[0], $length);
                return 23;
            } else if ($direction === "up") {
                $this->insertResponseToShots($userId, $gameId, $target, 24, array_keys($outputArray)[0], $length);
                return 24;
            }
        }

        $this->insertResponseToShots($userId, $gameId, $target, 1);
        return 1;
    }

    private function insertResponseToShots(int $userId, $gameId, string $target, int $response, $startCoord = NULL, $ship_length = NULL): void
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("INSERT INTO Shots (player_id, game_id, target, request, response, turn_number, shot_time, start_coord, ship_length) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->execute([$userId, $gameId, $target, 0, $response, $this->countTurns($gameId), date('Y-m-d H:i:s'), $startCoord, $ship_length]);
    }

    public function getWinnerFromGamesIfGameIsEnd($gameId, $login, $opponent): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT winner FROM Games WHERE id = ?");
        $statement->execute([$gameId]);
        $winner = intval($statement->fetchAll()[0]["winner"]);
        if ($winner !== 0) {
            return $winner;
        }

        $statement = $connection->prepare("SELECT COUNT(id) 'destroyed_count' FROM ShipsKorotkyi WHERE game_id = ? AND user_id = ? AND is_destroyed = 1");
        $statement->execute([$gameId, $this->getUserIdFromLogin($login)]);
        $destroyedCount = intval($statement->fetchAll()[0]["destroyed_count"]);

        $statement = $connection->prepare("SELECT COUNT(id) 'skips' FROM Shots WHERE game_id = ? AND player_id = ? AND target = 'afk'");
        $statement->execute([$gameId, $this->getUserIdFromLogin($login)]);
        $skips = intval($statement->fetchAll()[0]["skips"]);

        if ($destroyedCount === 10) {
            $this->updateUserStatusInQueues($login, 0);
            $statement = $connection->prepare("UPDATE Games SET winner = ? WHERE id = ?");
            $statement->execute([$this->getUserIdFromLogin($opponent), $gameId]);
            return $this->getUserIdFromLogin($opponent);
        } else if ($skips === 3) {
            $this->updateUserStatusInQueues($login, 0);
            $statement = $connection->prepare("UPDATE Games SET winner = ? WHERE id = ?");
            $statement->execute([$this->getUserIdFromLogin($login), $gameId]);
            return $this->getUserIdFromLogin($login);
        }
        return 0;
    }

    public function getWinnerFromGamesIfYouEndGame($gameId, $login, $opponent): int
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("SELECT winner FROM Games WHERE id = ?");
        $statement->execute([$gameId]);
        $winner = intval($statement->fetchAll()[0]["winner"]);
        if ($winner !== 0) {
            return $winner;
        }

        $statement = $connection->prepare("SELECT COUNT(id) 'destroyed_count' FROM Shots WHERE game_id = ? AND player_id = ? AND start_coord IS NOT NULL");
        $statement->execute([$gameId, $this->getUserIdFromLogin($opponent)]);
        $destroyedCount = intval($statement->fetchAll()[0]["destroyed_count"]);

        $statement = $connection->prepare("SELECT COUNT(id) 'skips' FROM Shots WHERE game_id = ? AND player_id = ? AND target = 'afk'");
        $statement->execute([$gameId, $this->getUserIdFromLogin($login)]);
        $skips = intval($statement->fetchAll()[0]["skips"]);
        if ($destroyedCount === 10) {
            $this->updateUserStatusInQueues($login, 0);
            $statement = $connection->prepare("UPDATE Games SET winner = ? WHERE id = ?");
            $statement->execute([$this->getUserIdFromLogin($login), $gameId]);
            return $this->getUserIdFromLogin($login);
        } else if ($skips === 3) {
            $this->updateUserStatusInQueues($login, 0);
            $statement = $connection->prepare("UPDATE Games SET winner = ? WHERE id = ?");
            $statement->execute([$this->getUserIdFromLogin($opponent), $gameId]);
            return $this->getUserIdFromLogin($opponent);
        } else {
            return 0;
        }
    }

    public function updateWinnerInGames($gameId, $login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("UPDATE Games SET winner = ? WHERE id = ?");
        $statement->execute([$this->getUserIdFromLogin($login), $gameId]);
    }

    public function getShipWithTargetAndWithDestroyed($gameId, $target, $login): array
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare(
            "SELECT GROUP_CONCAT(coordinate) 'coords' 
        FROM CoordinatesKorotkyi WHERE ship_id IN
        (SELECT id FROM ShipsKorotkyi WHERE user_id = ? AND start_coordinate IN 
        (SELECT start_coord FROM Shots WHERE game_id= ? AND response LIKE '2_' AND target = ?) AND game_id =?)");
        $statement->execute([$this->getUserIdFromLogin($login), $gameId, $target, $gameId]);
        $destroyedShipCoordinates = $statement->fetchAll()[0]["coords"];
        return explode(",", $destroyedShipCoordinates);
    }

    public function updateOnlineStatus($login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("UPDATE Users SET is_online=1 WHERE login = LOWER(?)");
        $statement->execute([$login]);
        $statement = $connection->prepare("SELECT user_id FROM Queues WHERE user_id = ?");
        $statement->execute([$this->getUserIdFromLogin($login)]);
        $userId = $statement->fetchAll()[0]["user_id"];
        if (!is_null($userId)) {
            $statement = $connection->prepare("INSERT INTO Queues (user_id, status) VALUES (?, 0)");
            $statement->execute([$this->getUserIdFromLogin($login)]);
        }
    }

    public function updateTimeInUsers($login)
    {
        $connection = $this->db->getConnection();
        $statement = $connection->prepare("UPDATE Users SET last_update=? WHERE login = LOWER(?)");
        $statement->execute([date("Y-m-d H:i:s"), $login]);
    }
}