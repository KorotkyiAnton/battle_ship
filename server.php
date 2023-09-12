<?php
header('Content-Type: application/json; charset=utf-8');
$unique = false;
$loginArr = ["anton", "denis", "anton1", "aboba", "vitya", "mikola"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postData = json_decode(file_get_contents('php://input'), true);

    if (!in_array($postData["login"], $loginArr)) {
        $unique = true;
    }
    $id = rand(0, 100);
    $date = new DateTime();
    $login = $postData['login'];
    echo json_encode([
        'messageId' => $id,
        'messageType' => 'isLoginUnique',
        'createDate' => new DateTime(),
        'isUnique' => $unique,
        'login' => $login
    ]);
} else {
    echo "no login";
}