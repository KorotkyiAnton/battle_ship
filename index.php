<?php

use Dotenv\Dotenv;
require_once __DIR__ . "/vendor/autoload.php";

ini_set('max_execution_time', '90');

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin', 'http://localhost:63342');
header('Access-Control-Allow-Credentials', 'true');
header('Content-Type: application/json; charset=utf-8');

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__); // Create a new instance of the Dotenv class to load environment variables
$dotenv->load(); // Load the environment variables from the .env file in the current directory

$messageHandler = new \app\MessageHandler(); // Create a new instance of the MessageHandler class

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the request method is POST
    $postData = json_decode(file_get_contents('php://input'), true); // Get the JSON data from the request body and decode it
    if(isset($postData["messageType"])) { // Check if the decoded JSON data contains a key named "messageType"
        try {
            $messageHandler->processMessage($postData["messageType"], $postData);
        } catch (Exception $e) {

        } // Call the processMessage method of the MessageHandler instance with the "messageType" value and the JSON data as arguments
    } else {
        http_response_code(400); // Return an HTTP response with the status code 400 (Bad Request)
    }
}