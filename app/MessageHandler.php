<?php

namespace app;

use Exception;

class MessageHandler
{
    /**
     * Обрабатывает сообщение, вызывая указанный метод в классе Controller
     * и кодируя результат в формате JSON.
     *
     * @param string $methodName Имя метода, который должен быть вызван в классе Controller.
     * @param array $postData Данные, которые должны быть переданы в качестве аргумента для метода.
     * @return void
     */
    public function processMessage(string $methodName, array $postData): void
    {
        // Создаем экземпляр класса Controller
        $controller = new Controller();
        // Проверяем, существует ли метод в классе Controller
        try {
            $result = call_user_func([$controller, $methodName], $postData);
            if ($result !== null) {
                echo json_encode($result);
            }
        } catch (Exception $e) {
            //ToDo: handle error
            http_response_code(400);
        }
    }
}