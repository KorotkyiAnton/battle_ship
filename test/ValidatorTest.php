<?php

namespace test;

use PHPUnit\Framework\TestCase;
use app\Validator;

class ValidatorTest extends TestCase
{
    /**
     * Тест для проверки корректного логина.
     */
    public function testValidLogin()
    {
        // Создание нового экземпляра класса Validator
        $validator = new Validator();

        // Определение корректного значения логина
        $login = "antony";

        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке пусто
        $this->assertEquals('', $errorMsg);
    }

    /**
     * Тест для некорректной длины логина.
     */
    public function testInvalidLoginLength()
    {
        $validator = new Validator();

        // Задание логина с слишком короткой длиной
        $login = "ab";

        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('дозволена довжина нікнейму від 3 до 10 символів', $errorMsg);
    }

    /**
     * Тест для некорректных символов в логине.
     */
    public function testInvalidLoginCharacters()
    {
        // Создание нового экземпляра класса Validator
        $validator = new Validator();

        // Задание логина с некорректными символами
        $login = "example#";

        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg);
    }

    /**
     * Тест для проверки обработки некорректного логина, начинающегося с особого символа.
     */
    public function testInvalidLoginStartsWithSpecialCharacter()
    {
        // Создание нового экземпляра класса Validator
        $validator = new Validator();

        // Задание логина, начинающегося с особого символа
        $login = "-example123";

        // Проверка логина и ожидание сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('починатися з літер чи цифр', $errorMsg);
    }

    /**
     * Тест для проверки обработки некорректного логина, заканчивающегося особым символом.
     */
    public function testInvalidLoginEndsWithSpecialCharacter()
    {
        // Создание экземпляра класса Validator
        $validator = new Validator();

        // Задание логина, заканчивающегося особым символом
        $login = "example123-";

        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('закінчуватися на літеру чи цифру', $errorMsg);
    }

    /**
     * Тест для проверки непервичного логина.
     *
     * Этот тест проверяет случай, когда предоставляется непервичный логин.
     * Создается новый экземпляр класса Validator, который используется для
     * проверки логина. Логин устанавливается как "existingLogin", и функция
     * проверяет, что сообщение об ошибке содержит определенную строку.
     */
    public function testNonUniqueLogin()
    {
        // Создание нового экземпляра класса Validator
        $validator = new Validator();

        // Установка логина как непервичного значения
        $login = "existingLogin";

        // Использование экземпляра Validator для проверки логина
        $errorMsg = $validator->validateLogin($login, false);

        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('данний нікнейм вже зайнятий', $errorMsg);
    }

    /**
     * Тест для проверки корректного логина с кириллическими символами.
     */
    public function testValidLoginWithCyrillic()
    {
        // Создание нового экземпляра класса Validator
        $validator = new Validator();
        // Определение логина с кириллическими символами
        $login = "ксююю юю";
        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);
        // Проверка, что сообщение об ошибке пусто
        $this->assertEquals('', $errorMsg);
    }

    /**
     * Тест для проверки некорректного логина с особым символом в начале и кириллическими символами.
     */
    public function testInvalidLoginWithSpecialCharacterAtStartWithCyrillic()
    {
        // Создание нового экземпляра класса Validator.
        $validator = new Validator();
        // Установка строки логина с особым символом в начале и кириллическими символами.
        $login = "-кириллица123";
        // Проверка логина и получение сообщения об ошибке.
        $errorMsg = $validator->validateLogin($login, true);
        // Проверка, что сообщение об ошибке содержит ожидаемую строку валидации.
        $this->assertStringContainsString('починатися з літер чи цифр', $errorMsg);
    }

    /**
     * Этот тест-кейс проверяет, что некорректный логин с особым символом в конце,
     * а также кириллическими символами, возвращает ожидаемое сообщение об ошибке.
     */
    public function testInvalidLoginWithSpecialCharacterAtEndWithCyrillic()
    {
        // Создание экземпляра класса Validator
        $validator = new Validator();

        // Определение строки логина с кириллическими символами и особым символом в конце
        $login = "кириллица123-";

        // Вызов метода validateLogin с логином и флагом, указывающим на поддержку кириллицы
        $errorMsg = $validator->validateLogin($login, true);

        // Проверка, что сообщение об ошибке содержит ожидаемую подстроку
        $this->assertStringContainsString('закінчуватися на літеру чи цифру', $errorMsg);
    }

    /**
     * Тест для проверки некорректного логина с особым символом посередине и кириллическими символами.
     */
    public function testInvalidLoginWithSpecialCharacterInMiddleWithCyrillic()
    {
        $validator = new Validator();
        // Установка логина с кириллическими символами и специальным символом в середині
        $login = "кир-`123";
        // Проверка логина и получение сообщения об ошибке
        $errorMsg = $validator->validateLogin($login, true);
        // Проверка, что сообщение об ошибке содержит ожидаемую строку
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg);
    }
}