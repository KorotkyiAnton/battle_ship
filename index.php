<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/app/Controller.php";
require_once __DIR__ . "/app/Model.php";
require_once __DIR__ . "/app/Logger.php";
require_once __DIR__ . "/vendor/autoload.php";

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
        $randNumber = rand(1, 100);
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

            for ($i; $i < 90; $i++) {
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

                sleep(1);
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
        $shotResponse = NULL;
        $userOnline = $controller->userOnline($model->getUserIdFromLogin($postData["opponent"]));
//        if(!$userOnline) {
//            for ($i=0; $i < 90; $i++) {
//                $userOnline = $controller->userOnline($model->getUserIdFromLogin($postData["opponent"]));
//                if($userOnline) {
//                    $shotResponse = $controller->getApprovalStatusFromOpponent($postData["gameId"], $postData["shotCoords"], $postData["login"]);
//                    break;
//                }
//            }
//        } else {
        $shotResponse = $controller->getApprovalStatusFromOpponent($postData["gameId"], $postData["shotCoords"], $postData["login"]);
//        }
        $winner= 0;

        if($shotResponse === 0 || $shotResponse === 21 || $shotResponse === 22 || $shotResponse === 23 || $shotResponse === 24) {
            $winner = $controller->getWinnerForRequester($postData["gameId"], $postData["login"], $postData["opponent"]);
        }


        echo json_encode([
            "messageId" => 14,
            "messageType" => "shotResponseCoords",
            "createDate" => new DateTime(),
            "shotResponse" => $shotResponse,
            "shotCoords" => $postData["shotCoords"],
            "winner" => $winner
        ]);
    } else if ($postData["messageType"] === "shotResponseCoords") {
        $shotResponse = $controller->listenRequestFromOpponent($postData["gameId"], $postData["login"]);
        $endOfTheGame = $controller->getWinnerOfGame($postData["gameId"], $postData["login"], $postData["opponent"]);

        echo json_encode([
            "messageId" => 16,
            "messageType" => "shotResponseCoords",
            "createDate" => new DateTime(),
            "shotResponse" => $shotResponse[1],
            "shotCoords" => $shotResponse[0],
            "winner" => $endOfTheGame
        ]);
    } else if ($postData["messageType"] === "userCancelPage") {
        $logger->log("User {$postData["login"]} exit games. All info is removed.");
        $controller->updateWinner($postData["gameId"], $postData["login"]);
        $controller->removePlayerFromQueue($postData["login"]);
    } else if ($postData["messageType"] === "userEnterPreviousPage") {
        $logger->log("User {$postData["login"]} exit games. All info is removed.");
        $controller->updateWinner($postData["gameId"], $postData["login"]);
        $controller->removePlayerFromQueue($postData["login"]);
    }
}
