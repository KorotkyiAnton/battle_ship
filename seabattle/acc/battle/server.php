<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postData = json_decode(file_get_contents('php://input'), true);
    $fieldWithShips = array(
        "fourdeck1" => array(
            "coords" => array("f1", "g1", "h1", "i1"),
            "hits" => 0,
            "shipStart" => "f1",
            "orientation" => "east"
        ),
        "tripledeck1" => array(
            "coords" => array("e4", "e5", "e6"),
            "hits" => 0,
            "shipStart" => "e4",
            "orientation" => "south"
        ),
        "tripledeck2" => array(
            "coords" => array("f9", "g9", "h9"),
            "hits" => 0,
            "shipStart" => "f9",
            "orientation" => "east"
        ),
        "doubledeck1" => array(
            "coords" => array("a5", "a6"),
            "hits" => 0,
            "shipStart" => "a5",
            "orientation" => "south"
        ),
        "doubledeck2" => array(
            "coords" => array("b1", "b2"),
            "hits" => 0,
            "shipStart" => "b1",
            "orientation" => "south"
        ),
        "doubledeck3" => array(
            "coords" => array("c8", "d8"),
            "hits" => 0,
            "shipStart" => "c8",
            "orientation" => "east"
        ),
        "singledeck1" => array(
            "coords" => array("g5"),
            "hits" => 0,
            "shipStart" => "g5",
            "orientation" => "east"
        ),
        "singledeck2" => array(
            "coords" => array("g7"),
            "hits" => 0,
            "shipStart" => "g7",
            "orientation" => "south"
        ),
        "singledeck3" => array(
            "coords" => array("d2"),
            "hits" => 0,
            "shipStart" => "d2",
            "orientation" => "south"
        ),
        "singledeck4" => array(
            "coords" => array("i3"),
            "hits" => 0,
            "shipStart" => "i3",
            "orientation" => "south"
        )
    );

    function checkIsHit($ships, $shot): int
    {
        $isHit = 0;
        foreach ($ships as $ship) {
            if (in_array($shot, $ship["coords"])) {
                $isHit = 1;
            }
            $isHit = $shot === "h9"? 2: $isHit;
        }
        return $isHit;
    }

    function calculateCoordinate($turn): string
    {
        $letters = range('a', 'j');
        $numbers = range(1, 10);

        $letterIndex = ($turn - 1) % count($letters);
        $numberIndex = floor(($turn - 1) / count($letters)) % count($numbers);

        $letter = $letters[$letterIndex];
        $number = $numbers[$numberIndex];

        return $letter . $number;
    }

    if ($postData["messageType"] === "shotRequest") {
        if ($postData["request"] !== 0) {
            echo json_encode([
                "messageId" => 11,
                "messageType" => "shotResponse",
                "createDate" => new DateTime(),
                "response" => checkIsHit($fieldWithShips, $postData["request"])
            ]);
        } else {
            sleep(2);
            echo json_encode([
                "messageId" => 11,
                "messageType" => "shotRequest",
                "createDate" => new DateTime(),
                "request" => calculateCoordinate($postData["turn"])
            ]);
        }
    } else if ($postData["messageType"] === "shotResponse" && $postData["response"] === 1) {
        echo json_encode("opponent hit ".$postData["coordinate"]);
    } else if ($postData["messageType"] === "shotResponse" && $postData["response"] === 0) {
        echo json_encode("opponent miss ".$postData["coordinate"]);
    }
}