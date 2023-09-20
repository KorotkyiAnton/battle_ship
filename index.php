<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/app/Controller.php";
require_once __DIR__ . "/app/Model.php";
require_once __DIR__."/vendor/autoload.php";

header('Content-Type: application/json; charset=utf-8');

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__); // Adjust the path accordingly
$dotenv->load();

$controller = new \app\Controller();
$model = new \app\Model();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    if (isset($postData["login"])) {
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
}
