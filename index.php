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
    } else if (isset($postData["messageType"]) && $postData["messageType"] === "requestIsUsersInQueue") {
        $userStatusInSearch = $controller->updateUserStatusInQueues($postData["login"]);
        $userIdInSearch = $controller->findUsersThatSearchForGame($postData["login"]);
        echo json_encode([
            "userStatus" => $userStatusInSearch,
            "userInSearch" => $userIdInSearch
        ]);
        if ($userIdInSearch) { //SELECT user_id FROM Queues WHERE status=1 LIMIT=1
            //INSERT INTO Games (first_player, first_turn) VALUES (4, 52)
            $newGameId = $controller->createNewGame($postData["login"]);
            for ($i = 0; $i < 90; $i++) {
                $userIdInSearch = $controller->findUsersThatSearchForGame($postData["login"]);
                if ($userIdInSearch) {
                    echo json_encode([
                        "messageId" => 9,
                        "messageType" => "gameCreateInfo",
                        "createDate" => new DateTime(),
                        "game_id" => rand(1, 100),
                        "second_player_login" => $second_player_login,
                        "your_turn" => (bool)rand(0, 1),
                        "time_for_search" => $i,
                        "ships" => $postData["ships"]
                    ]);
                    break;
                }
                sleep(1);
            }
        }
// else {
//            //SELECT id FROM Games WHERE first_player = user_id
//            //UPDATE Games SET second_player = user_id2, first_turn = CASE WHEN first_turn < user_input THEN user_input ELSE first_turn
//            $first_player_login = $logins[rand(0,5)];//SELECT login FROM Users WHERE id = $isUserPresentInQueue
//            echo json_encode([
//                "messageId" => 9,
//                "messageType" => "gameConnectInfo",
//                "createDate" => new DateTime(),
//                "game_id" => rand(1, 100),
//                "first_player_login" => $first_player_login,
//                "your_turn" => (bool)rand(0, 1),
//                "ships" => $postData["ships"]
//            ]);
//        }
    }
}
