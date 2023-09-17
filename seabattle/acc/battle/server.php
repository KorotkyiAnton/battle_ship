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

    function checkIsHit($ships, $shot)
    {
        $isHit = 0;
        foreach ($ships as $ship) {
            if(in_array($shot, $ship["coords"])) {
                $isHit = 1;
            }
        }
        return $isHit;
    }

    if($postData["messageType"] === "shotRequest") {
        echo json_encode([
            "messageId" => 11,
            "messageType" => "shotResponse",
            "createDate" => new DateTime(),
            "response" => checkIsHit($fieldWithShips, $postData["request"])
        ]);
    }
}