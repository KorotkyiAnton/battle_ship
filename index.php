<?php
require_once __DIR__."/app/Controller.php";
require_once __DIR__."/app/Model.php";

$controller = new \app\Controller();
$model = new \app\Model();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);
    if(isset($postData["login"])) {
        $validationResult = $controller->validateLogin($postData["login"]);
        if($validationResult) {
            echo json_encode([
                'messageId' => 1,
                'messageType' => 'isLoginUnique',
                'createDate' => new DateTime(),
                'isUnique' => true,
                'login' => $postData["login"],
                //'status' => $status
            ]);
        }
    }
}
echo  $controller->validateLogin("Anton");
