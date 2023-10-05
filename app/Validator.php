<?php

namespace app;

class Validator
{
    /**
     * Проверяет логин на валидность.
     *
     * @param string $login Логин для проверки.
     * @param bool $loginUnique Флаг, указывающий, должен ли логин быть уникальным.
     * @return string Если логин действителен, возвращает пустую строку; в противном случае возвращает сообщение об ошибке.
     */
    public function validateLogin(string $login, bool $loginUnique): string
    {
        //ToDo: собирать в словарь и пулить в json
        $errorMsg = '';
        // Проверяем длину логина от 3 до 10 символов
        $errorMsg .= $this->checkIsLoginLengthFrom3To10($login);
        // Проверяем, состоит ли логин только из букв и цифр
        $errorMsg .= $this->checkIsLoginConsistSpecialSymbols($login);
        // Проверяем, начинается ли логин с буквы
        $errorMsg .= $this->checkFirstLetterInLogin($login);
        // Проверяем, заканчивается ли логин буквой
        $errorMsg .= $this->checkLastLetterInLogin($login);
        // Проверяем, является ли логин уникальным
        $errorMsg .= $this->checkIsLoginUnique($loginUnique);

        return $errorMsg;
    }

    /**
     * Проверяет, находится ли длина логина в диапазоне от 3 до 10 символов.
     *
     * @param string $login Логин для проверки.
     * @return string Возвращает сообщение об ошибке, если длина логина не находится в диапазоне от 3 до 10 символов, иначе возвращает пустую строку.
     */
    private function checkIsLoginLengthFrom3To10(string $login): string
    {
        $regExp = preg_match("/^(.{3,10})$/u", $login);

        if (false === $regExp) {
            return "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        }

        return "";
    }

    /**
     * Проверяет, содержит ли строка логина специальные символы.
     *
     * @param string $login Строка логина для проверки.
     * @return string Сообщение об ошибке, если логин содержит недопустимые символы, в противном случае возвращает пустую строку.
     */
    private function checkIsLoginConsistSpecialSymbols(string $login): string
    {
        $regExp = preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-'_ ]+$/u", $login);

        if (false === $regExp) {
            return "На жаль, помилка - нікнейм може містити літери (zZ-яЯ), цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        }

        return "";
    }

    /**
     * Проверяет, начинается ли первая буква логина с буквы или цифры.
     *
     * @param string $login Логин для проверки.
     * @return string Возвращает сообщение об ошибке, если первая буква не является буквой или цифрой, иначе возвращает пустую строку.
     */
    private function checkFirstLetterInLogin(string $login): string
    {
        $regExp = preg_match("/^[А-яA-Za-zЁёЇїІіЄєҐґ]/u", $login);

        if (false === $regExp) {
            return "Нікнейм повинен починатися з літер чи цифр;";
        }

        return "";
    }

    /**
     * Проверяет, заканчивается ли данная строка логина буквой или цифрой.
     *
     * @param string $login Логин для проверки.
     * @return string Возвращает сообщение об ошибке, если логин не заканчивается буквой или цифрой, иначе возвращает пустую строку.
     */
    private function checkLastLetterInLogin(string $login): string
    {
        $regExp = preg_match("/[0-9А-яA-Za-zЁёЇїІіЄєҐґ]$/u", $login);

        if (false === $regExp) {
            return "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        }

        return "";
    }

    /**
     * Проверяет, является ли логин уникальным, и возвращает сообщение об ошибке, если он не уникален.
     *
     * @param bool $loginUnique Флаг, указывающий, является ли логин уникальным.
     * @return string Сообщение об ошибке, если логин не уникален, иначе возвращает пустую строку.
     */
    private function checkIsLoginUnique(bool $loginUnique): string
    {
        if (!$loginUnique) {
            return "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте інший.<br>";
        }

        return "";
    }
}