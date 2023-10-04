<?php

namespace app;

class MessageHandler
{
    /**
     * Processes a message by calling the specified method on the Controller class
     * and encoding the result as JSON.
     *
     * @param string $methodName The name of the method to be called on the Controller class.
     * @param array $postData The data to be passed as an argument to the method.
     * @return void
     */
    public function processMessage(string $methodName, array $postData): void
    {
        // Instantiate the Controller class
        $controller = new Controller();
        // Check if the method exists in the Controller class
        if (method_exists($controller, $methodName)) {
            // Call the specified method on the Controller class passing the $postData
            $result = call_user_func([$controller, $methodName], $postData);
            // If the result is not null, encode it as JSON and echo it
            if ($result !== null) {
                echo json_encode($result);
            }
        } else {
            // If the method does not exist, set the HTTP response code to 400
            http_response_code(400);
        }
    }
}