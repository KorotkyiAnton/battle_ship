<?php

namespace app;
require_once __DIR__ . "/Model.php";

class Controller
{

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function validateLogin($login): string
    {
        $errorMsg = "";

        // Проверка длины логина
        if (strlen($login) < 3 || strlen($login) > 10) {
            $errorMsg .= "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        }

        // Проверка символов логина
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-\'_]+$/", $login)) {
            $errorMsg .= "На жаль, помилка - нікнейм може містити літери (zZ-яЯ),цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        }

        // Проверка начального символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/", $login[0])) {
            $errorMsg .= "Нікнейм повинен починатися з літер чи цифр;";
        }

        // Проверка конечного символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]$/", $login[strlen($login) - 1])) {
            $errorMsg .= "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        }

        if (!$this->checkUnique($login)) {
            $errorMsg .= "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте інший.<br>";
        }

        if ($errorMsg === "") {
            $this->model->addLoginToDB($login);
        }

        return $errorMsg;
    }

    private function checkUnique($login): bool
    {
        return $this->model->isLoginUnique($login);
    }

    public function checkUserStatusOnQueue($login): int
    {
        return $this->model->getUserStatusFromQueues($login);
    }
}