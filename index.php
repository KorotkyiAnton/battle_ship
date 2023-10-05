<?php

use Dotenv\Dotenv;

require_once __DIR__ . "/vendor/autoload.php";

ini_set('max_execution_time', '90');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin', 'http://localhost:63342');
header('Access-Control-Allow-Credentials', 'true');
header('Content-Type: application/json; charset=utf-8');

// Загрузка переменных окружения из файла .env
$dotenv = Dotenv::createImmutable(__DIR__); // Создаем новый экземпляр класса Dotenv для загрузки переменных окружения
$dotenv->load(); // Загружаем переменные окружения из файла .env в текущем каталоге

$messageHandler = new \app\MessageHandler(); // Создаем новый экземпляр класса MessageHandler

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Only POST requests are allowed");
}

$postData = json_decode(file_get_contents('php://input'), true);
try {
    $messageHandler->processMessage($postData["messageType"], $postData);
} catch (Exception $e) {
    //ToDo: handle error
    http_response_code(400);
    die("Error processing message: " . $e->getMessage());
}