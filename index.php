<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/app/Controller.php";
require_once __DIR__ . "/app/Model.php";
require_once __DIR__ . "/app/Logger.php";
require_once __DIR__ . "/vendor/autoload.php";

error_reporting(E_ERROR);
ini_set('max_execution_time', '300');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin', 'http://localhost:63342');
header('Access-Control-Allow-Credentials', 'true');


header('Content-Type: application/json; charset=utf-8');

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__); // Adjust the path accordingly
$dotenv->load();

$controller = new \app\Controller();
$model = new \app\Model();
$logger = new \app\Logger();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    if (isset($postData["login"]) && $postData["messageType"] === "isLoginUnique") {
        $validationResult = "";
        $validationResult = $controller->validateLogin($postData["login"]);
        $controller->updateOnlineStatus($postData["login"]);
        $logger->log("User {$postData["login"]} validate login in server with result: $validationResult");
        $status = 0;
        $status = $controller->checkUserStatusOnQueue($postData["login"]);
        $logger->log("User {$postData["login"]} check if he reconnects to game. Now user status in Queue is: $status");

        echo json_encode([
            'messageId' => 5,
            'messageType' => 'loginRegisteredInDB',
            'createDate' => new DateTime(),
            'isWriteToDB' => !($validationResult),
            'errMsg' => $validationResult,
            'login' => $postData["login"],
            'status' => $status
        ]);
    } else if (isset($postData["messageType"]) && $postData["messageType"] === "requestIsUsersInQueue") {
        $userStatusInSearch = $controller->updateUserStatusInQueues($postData["login"], 1);
        $logger->log("User {$postData["login"]} stand in Queue with status $userStatusInSearch");
        $userIdInSearch = $controller->findUsersThatSearchForGame($postData["login"]);
        $randNumber = rand(1, 1000);
        $logger->log("User {$postData["login"]} gets $randNumber on randomizer");
        if (!$userIdInSearch) { //SELECT user_id FROM Queues WHERE status=1 LIMIT=1
            $newGame = 0;
            $i = 0;
            if ($postData["continueSearch"]) {
                //INSERT INTO Games (first_player, first_turn) VALUES (4, 52)
                $newGame = $controller->createNewGame($postData["login"], $randNumber);
                $logger->log("User {$postData["login"]} create new game with id = $newGame");
            } else {
                $controller->deleteEmptyGame($postData["login"]);
                $controller->updateUserStatusInQueues($postData["login"], 0);
                $i = 90;
                $logger->log("User {$postData["login"]} leave Queue");
            }

            for ($i; $i < 450; $i++) {
                $userIdInSearch = $controller->checkSecondUserConnect($newGame);
                if ($userIdInSearch) {
                    $firstPlayerId = $model->getUserIdFromLogin($postData["login"]);
                    $secondPlayerLogin = $controller->getSecondUserLogin($userIdInSearch);
                    $controller->updateUserStatusInQueues($postData["login"], 2);
                    $newGameId = $newGame;
                    $controller->addShipsAndCoordinates($postData["shipCoordinates"], $newGameId, $postData["login"]);
                    $firstTurn = $controller->getFirstTurnFromDB($newGameId);
                    $logger->log("User {$postData["login"]} find opponent with name $secondPlayerLogin. " .
                        ($firstTurn === $randNumber ? 'User turn first' : 'User turn second'));
                    echo json_encode([
                        "messageId" => 10,
                        "messageType" => "gameCreateInfo",
                        "createDate" => new DateTime(),
                        "game_id" => $newGameId,
                        "opponent_login" => $secondPlayerLogin,
                        "your_turn" => $firstTurn < $randNumber,
                        "time_for_search" => $i
                    ]);
                    break;
                }

                usleep(200000);
            }
            if ($i === 90) {
                $logger->log("User {$postData["login"]} cant find opponents");
                $controller->deleteEmptyGame($postData["login"]);
                if($controller->checkUserStatusOnQueue($postData["login"]) !== 2) {
                    $controller->updateUserStatusInQueues($postData["login"], 0);
                }
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
            $secondPlayerId = $model->getUserIdFromLogin($postData["login"]);
            $firstPlayerLogin = $controller->getSecondUserLogin($userIdInSearch);//SELECT login FROM Users WHERE id = $isUserPresentInQueue
            $connectToGame = $controller->connectToGame($postData["login"], $userIdInSearch, $randNumber);
            $connectGameId = $connectToGame[0];
            $logger->log("User {$postData["login"]} connect to game with id = $connectGameId");
            /***
             * ToDo: uncomment controller method when I start test app with real people
             */
            $controller->addShipsAndCoordinates($postData["shipCoordinates"], $connectGameId, $postData["login"]);
            $controller->updateUserStatusInQueues($postData["login"], 2);
            $firstTurn = $connectToGame[1];

            echo json_encode([
                "messageId" => 10,
                "messageType" => "gameConnectInfo",
                "createDate" => new DateTime(),
                "game_id" => $connectGameId,
                "opponent_login" => $firstPlayerLogin,
                "your_turn" => $firstTurn < $randNumber
            ]);
        }
    } else if ($postData["messageType"] === "exitFromPage") {
        $logger->log("User {$postData["login"]} exit games. All info is removed.");
        $controller->deleteEmptyGame($postData["login"]);
        $controller->removePlayerFromQueue($postData["login"]);
        $controller->removePlayerFromUserList($postData["login"]);
    } else if ($postData["messageType"] === "localShipStoreEmpty") {
        $logger->log("User {$postData["login"]} get info from private table");
        $shipsSquadron = $controller->formShipsJSON($postData["login"]);
        $gameInfo = $controller->getCurrentGameInfo($postData["login"]);
        $gameId = $gameInfo[0];
        $playerId = $gameInfo[1];
        $opponentLogin = $gameInfo[2];
        $firstTurn = $gameInfo[3];

        echo json_encode([
            "messageId" => 12,
            "messageType" => "shipsFromDB",
            "createDate" => new DateTime(),
            "game_id" => $gameId,
            "opponent_login" => $opponentLogin,
            "your_turn" => $firstTurn,
            "shipCoordinates" => $shipsSquadron
        ]);
    } else if ($postData["messageType"] === "shotRequestCoords") {
        $controller->sendShotToOpponent($postData["gameId"], $postData["shotCoords"], $postData["login"]);
        $userOnline = $controller->userOnline($model->getUserIdFromLogin($postData["opponent"]));
        if($userOnline > 5) {
            for ($i=0; $i < 90; $i++) {
                $userOnline = $controller->userOnline($model->getUserIdFromLogin($postData["opponent"]));
                if($userOnline < 5) {
                    $shotResponse = $controller->getApprovalStatusFromOpponent($postData["gameId"], $postData["shotCoords"], $postData["login"]);
                    break;
                }
                sleep(1);
            }
        } else {
        $shotResponse = $controller->getApprovalStatusFromOpponent($postData["gameId"], $postData["shotCoords"], $postData["login"]);
        }
        $winner= 0;
        $response = intval($shotResponse[0]);

        if($response === 0 || $response === 21 || $response === 22 || $response === 23 || $response === 24) {
            $winner = $controller->getWinnerForRequester($postData["gameId"], $postData["login"], $postData["opponent"]);
        }

        $yourTurn = 0;

        if($shotResponse[0] > 0) {
            $yourTurn = 1;
        }

        $logger->log("User {$postData["login"]} strike cell ".$postData["shotCoords"]." with result ".$shotResponse[0]);
        echo json_encode([
            "messageId" => 14,
            "messageType" => "shotResponseCoords",
            "createDate" => new DateTime(),
            "shotResponse" => $shotResponse[0],
            "shotCoords" => $postData["shotCoords"],
            "ships" => $shotResponse[1],
            "winner" => $controller->getSecondUserLogin($winner),
            "yourTurn" => $yourTurn
        ]);
    } else if ($postData["messageType"] === "shotResponseCoords") {
        $shotResponse = $controller->listenRequestFromOpponent($postData["gameId"], $postData["login"]);
        $endOfTheGame = $controller->getWinnerOfGame($postData["gameId"], $postData["login"], $postData["opponent"]);
        $yourTurn = 1;
        $destroyedShip = null;

        if($shotResponse[1] > 0) {
            $yourTurn = 0;
        }
        if($shotResponse[1] > 20) {
            $destroyedShip = $controller->getDestroyedShip($postData["gameId"], $shotResponse[0], $postData["login"]);
        }

        $logger->log("User {$postData["opponent"]} strike cell ".$shotResponse[0]." with result ".$shotResponse[1]);
        echo json_encode([
            "messageId" => 16,
            "messageType" => "shotResponseCoords",
            "createDate" => new DateTime(),
            "shotResponse" => $shotResponse[1],
            "shotCoords" => $shotResponse[0],
            "ships"=> $destroyedShip,
            "winner" => $controller->getSecondUserLogin($endOfTheGame),
            "yourTurn" => $yourTurn
        ]);
    } else if ($postData["messageType"] === "userCancelPage") {
        $logger->log("User {$postData["login"]} exit games. All info is removed.");
        $controller->updateWinner($postData["gameId"], $postData["login"]);
        $controller->sendShotToOpponent($postData["gameId"], "afk", $postData["login"]);
        $controller->removePlayerFromQueue($postData["login"]);
    } else if ($postData["messageType"] === "userEnterPreviousPage") {
        $logger->log("User {$postData["login"]} exit games. All info is removed.");
        $controller->updateWinner($postData["gameId"], $postData["login"]);
        $controller->updateUserStatusInQueues($postData["login"], 0);
    } else if($postData["messageType"] === "lastUpdate") {
        $controller->updateLastTime($postData["login"]);
        $userOnline = $controller->userOnline($model->getUserIdFromLogin($postData["opponent"]));
        echo json_encode([
            "messageId" => 21,
            "messageType" => "lastUpdate",
            "createDate" => new DateTime(),
            "userOnline" => $userOnline
        ]);
    } else if($postData["messageType"] === "updateWinner") {
        $controller->updateWinner($postData["gameId"], $postData["login"]);
    } else if($postData["messageType"] === "getWinner") {
        $winner = $controller->getWinner($postData["gameId"], $postData["login"], $postData["opponent"]);
        echo json_encode([
            "messageId" => 22,
            "messageType" => "getWinner",
            "createDate" => new DateTime(),
            "winner" => $controller->getSecondUserLogin($winner)
        ]);
    }
}
