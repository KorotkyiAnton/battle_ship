<?php

namespace app;

class Validator
{
    /**
     * Validates the login.
     *
     * @param string $login The login to validate.
     * @param bool $loginUnique Whether the login should be unique.
     * @return string The error message is empty if the login is valid; otherwise, returns an error message.
     */
    public function validateLogin(string $login, bool $loginUnique): string
    {
        $errorMsg = '';
        // Check if login length is between 3 and 10 characters
        $errorMsg .= $this->checkIsLoginLengthFRom3To10($login);
        // Check if login consists of only alphanumeric characters
        $errorMsg .= $this->checkIsLoginConsistSpecialSymbols($login);
        // Check if login starts with a letter
        $errorMsg .= $this->checkFirstLetterInLogin($login);
        // Check if login ends with a letter
        $errorMsg .= $this->checkLastLetterInLogin($login);
        // Check if login is unique
        $errorMsg .= $this->checkIsLoginUnique($loginUnique);

        return $errorMsg;
    }

    /**
     * Checks if the length of the login is between 3 and 10 characters.
     *
     * @param string $login The login to be checked.
     * @return string Returns an error message if the login length is not between 3 and 10 characters; otherwise, returns an empty string.
     */
    private function checkIsLoginLengthFrom3To10(string $login): string
    {
        // Use regular expression to match the login length between 3 and 10 characters
        if (!preg_match("/^(.{3,10})$/u", $login)) {
            // Return an error message if the login length is not between 3 and 10 characters
            return "Нажаль, помилка - дозволена довжина нікнейму від 3 до 10 символів;<br>";
        } else {
            // Return an empty string if the login length is between 3 and 10 characters
            return "";
        }
    }

    /**
     * Checks if the login string contains special symbols.
     *
     * @param string $login The login string to check.
     * @return string An error message if the login contains invalid characters, otherwise an empty string.
     */
    private function checkIsLoginConsistSpecialSymbols(string $login): string
    {
        // Regex pattern that allows letters, numbers, spaces, dashes, apostrophes, and underscores
        $pattern = "/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ\-'_ ]+$/u";

        if (!preg_match($pattern, $login)) {
            // Return an error message if the login contains invalid characters
            return "На жаль, помилка - нікнейм може містити літери (zZ-яЯ), цифри (0-9), спецсимволи (Word space, -, ', _);<br>";
        } else {
            // Return an empty string if the login is valid
            return "";
        }
    }

    /**
     * Checks if the first letter of the login is a letter or a digit.
     *
     * @param string $login The login to check.
     * @return string Returns an error message if the first letter is not a letter or digit, otherwise returns an empty string.
     */
    private function checkFirstLetterInLogin(string $login): string
    {
        // Use regular expression to check if the first letter is a letter or digit
        if (!preg_match("/^[0-9А-яA-Za-zЁёЇїІіЄєҐґ]/u", $login)) {
            return "Нікнейм повинен починатися з літер чи цифр;";
        } else {
            return "";
        }
    }

    /**
     * Checks if the last character of the given login is a letter or digit.
     *
     * @param string $login The login to be checked.
     * @return string Returns an error message if the login does not end with a letter or digit, or an empty string otherwise.
     */
    private function checkLastLetterInLogin(string $login): string
    {
        // Use regex to match any letter or digit at the end of the login
        if (!preg_match("/[0-9А-яA-Za-zЁёЇїІіЄєҐґ]$/u", $login)) {
            return "Нікнейм повинен закінчуватися на літеру чи цифру;<br>";
        } else {
            return "";
        }
    }

    /**
     * Check if the login is unique and return an error message if it is not.
     *
     * @param bool $loginUnique Whether the login is unique or not
     * @return string Error message if the login is not unique, empty string otherwise
     */
    private function checkIsLoginUnique(bool $loginUnique): string
    {
        // Return error message if the login is not unique
        if (!$loginUnique) {
            return "На жаль, помилка - данний нікнейм вже зайнятий, спробуйте інший.<br>";
        } else {
            return "";
        }
    }
}