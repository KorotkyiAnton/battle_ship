<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postData = json_decode(file_get_contents('php://input'), true);
    $logins = ["anton", "denis", "anton1", "aboba", "vitya", "mikola"];

    $isUserPresentInQueue = rand(0, 1); //SELECT DISTINCT user_id FROM Queues WHERE status=1


    if (rand(0,4)===0) { //$isUserPresentInQueue
        //INSERT INTO Games (first_player, first_turn) VALUES (4, 52)
        for ($i = 0; $i < 90; $i++) {
            $isUserPresentInQueue = rand(0, 1); //SELECT DISTINCT user_id FROM Queues WHERE status=1
            $second_player_login = $logins[rand(0,5)];//SELECT login FROM Users WHERE id = $isUserPresentInQueue
            if ($isUserPresentInQueue) {
                echo json_encode([
                    "messageId" => 9,
                    "messageType" => "gameCreateInfo",
                    "createDate" => new DateTime(),
                    "game_id" => rand(1, 100),
                    "second_player_login" => $second_player_login,
                    "your_turn" => (bool)rand(0, 1),
                    "time_for_search" => $i
                ]);
                break;
            }
            sleep(1);
        }
        echo json_encode([
            "messageId" => 9,
            "messageType" => "usersNotFound",
            "createDate" => new DateTime()
        ]);
    } else {
        //SELECT id FROM Games WHERE first_player = user_id
        //UPDATE Games SET second_player = user_id2, first_turn = CASE WHEN first_turn < user_input THEN user_input ELSE first_turn
        $first_player_login = $logins[rand(0,5)];//SELECT login FROM Users WHERE id = $isUserPresentInQueue
        echo json_encode([
            "messageId" => 9,
            "messageType" => "gameConnectInfo",
            "createDate" => new DateTime(),
            "game_id" => rand(1, 100),
            "first_player_login" => $first_player_login,
            "your_turn" => (bool)rand(0, 1)
        ]);
    }
}