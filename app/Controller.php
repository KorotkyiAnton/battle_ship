<?php

namespace app;
require_once __DIR__."/Model.php";

class Controller
{

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function validateLogin($login): bool
    {
        $isError = false;

        // Проверка длины логина
        if (strlen($login) < 3 || strlen($login) > 10) {
            $isError = true;
        }

        // Проверка символов логина
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-\'_]+$/", $login)) {
            $isError = true;
        }

        // Проверка начального символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/", $login[0])) {
            $isError = true;
        }

        // Проверка конечного символа
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]$/", $login[strlen($login) - 1])) {
            $isError = true;
        }

        if(!$this->checkUnique($login)) {
            $isError = true;
        }

        if(!$isError) {
            $this->model->addLoginToDB($login);
        }

        return $isError;
    }

    private function checkUnique($login): bool
    {
        return $this->model->isLoginUnique($login);
    }
}