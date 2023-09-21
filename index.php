<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/app/Controller.php";
require_once __DIR__ . "/app/Model.php";
require_once __DIR__ . "/vendor/autoload.php";

header('Content-Type: application/json; charset=utf-8');

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__); // Adjust the path accordingly
$dotenv->load();

$controller = new \app\Controller();
$model = new \app\Model();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    if (isset($postData["login"]) && $postData["messageType"] === "isLoginUnique") {
        $validationResult = "";
        $validationResult = $controller->validateLogin($postData["login"]);
        $status = 0;
        $status = $controller->checkUserStatusOnQueue($postData["login"]);

        echo json_encode([
            'messageId' => 5,
            'messageType' => 'loginRegisteredInDB',
            'createDate' => new DateTime(),
            'isWriteToDB' => !($validationResult),
            'errMsg' => $validationResult,
            'login' => $postData["login"],
            'status' => $status
        ]);
    }
    else if (isset($postData["messageType"]) && $postData["messageType"] === "requestIsUsersInQueue") {
        $userStatusInSearch = $controller->updateUserStatusInQueues($postData["login"], 1);
        $userIdInSearch = $controller->findUsersThatSearchForGame($postData["login"]);
        $randNumber = rand(1, 100);
        if (!$userIdInSearch) { //SELECT user_id FROM Queues WHERE status=1 LIMIT=1
            $newGame = 0;
            $i = 0;
            if ($postData["continueSearch"]) {
                //INSERT INTO Games (first_player, first_turn) VALUES (4, 52)
                $newGame = $controller->createNewGame($postData["login"], $randNumber);
            } else {
                $controller->deleteEmptyGame($postData["login"]);
                $controller->updateUserStatusInQueues($postData["login"], 0);
                $i = 90;
            }

            for ($i; $i < 90; $i++) {
                $userIdInSearch = $controller->findUsersThatSearchForGame($postData["login"]);
                if ($userIdInSearch) {
                    $second_player_login = $controller->getSecondUserLogin($userIdInSearch);
                    $controller->updateUserStatusInQueues($postData["login"], 2);
                    $controller->updateUserStatusInQueues($second_player_login, 2);
                    $newGameId = $newGame;
                    $controller->addShipsAndCoordinates($postData["shipCoordinates"], $newGameId);
                    $firstTurn = $controller->getFirstTurnFromDB($newGameId);
                    echo json_encode([
                        "messageId" => 10,
                        "messageType" => "gameCreateInfo",
                        "createDate" => new DateTime(),
                        "game_id" => $newGameId,
                        "second_player_login" => $second_player_login,
                        "your_turn" => $firstTurn === $randNumber,
                        "time_for_search" => $i
                    ]);
                    break;
                }

                sleep(1);
            }
            if ($i === 90) {
                $controller->deleteEmptyGame($postData["login"]);
                $controller->updateUserStatusInQueues($postData["login"], 0);
                echo json_encode([
                    "messageId" => 10,
                    "messageType" => "gameNotFoundInfo",
                    "createDate" => new DateTime(),
                    "errMsg" => "Ми не знайшли гру. Спробуй ще!",
                    "time_for_search" => $i
                ]);
            }
        } else {
            //SELECT id FROM Games WHERE first_player = user_id
            //UPDATE Games SET second_player = user_id2, first_turn = CASE WHEN first_turn < user_input THEN user_input ELSE first_turn
            $first_player_login = $controller->getSecondUserLogin($userIdInSearch);//SELECT login FROM Users WHERE id = $isUserPresentInQueue
            $connectToGame = $controller->connectToGame($postData["login"], $userIdInSearch, $randNumber);
            $connectGameId = $connectToGame[0];
            /***
             * ToDo: uncomment controller method when I start test app with real people
             */
            $controller->addShipsAndCoordinates($postData["shipCoordinates"], $connectGameId);
            $firstTurn = $connectToGame[1];

            echo json_encode([
                "messageId" => 10,
                "messageType" => "gameConnectInfo",
                "createDate" => new DateTime(),
                "game_id" => $connectGameId,
                "first_player_login" => $first_player_login,
                "your_turn" => $firstTurn === $randNumber
            ]);
        }
    } else if($postData["messageType"] === "exitFromPage") {
        $controller->deleteEmptyGame($postData["login"]);
        $controller->removePlayerFromQueue($postData["login"]);
        $controller->removePlayerFromUserList($postData["login"]);
    }
}
