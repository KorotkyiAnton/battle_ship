<?php

namespace test;

use PHPUnit\Framework\TestCase;
use app\Validator;

class ValidatorTest extends TestCase
{
    /**
     * Test case for validating a valid login.
     */
    public function testValidLogin()
    {
        // Create a new instance of the Validator class
        $validator = new Validator();

        // Define a valid login value
        $login = "antony";

        // Validate the login and get the error message
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message is empty
        $this->assertEquals('', $errorMsg);
    }

    /**
     * Test case for invalid login length.
     */
    public function testInvalidLoginLength()
    {
        $validator = new Validator();

        // Set up login with too short length
        $login = "ab";

        // Validate login and get error message
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message contains the expected string
        $this->assertStringContainsString('дозволена довжина нікнейму від 3 до 10 символів', $errorMsg);
    }

    /**
     * Test for invalid login characters.
     */
    public function testInvalidLoginCharacters()
    {
        // Create a new instance of the Validator class
        $validator = new Validator();

        // Set the login with invalid characters
        $login = "example#";

        // Validate the login and get the error message
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message contains the expected string
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg);
    }

    /**
     * Test case to validate that an invalid login starting with a special character is handled correctly.
     */
    public function testInvalidLoginStartsWithSpecialCharacter()
    {
        // Create a new instance of the Validator class
        $validator = new Validator();

        // Define a login that starts with a special character
        $login = "-example123";

        // Validate the login and expect an error message
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message contains the expected string
        $this->assertStringContainsString('починатися з літер чи цифр', $errorMsg);
    }

    /**
     * Test case to verify if an invalid login ending with a special character is handled correctly.
     */
    public function testInvalidLoginEndsWithSpecialCharacter()
    {
        // Instantiate the Validator class
        $validator = new Validator();

        // Set the test login input
        $login = "example123-";

        // Validate the login and get the error message
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message contains the expected string
        $this->assertStringContainsString('закінчуватися на літеру чи цифру', $errorMsg);
    }

    /**
     * Test case for checking non-unique login.
     *
     * This function tests the case where a non-unique login is provided.
     * It creates a new instance of the Validator class and uses it to
     * validate the login. The login is set to "existingLogin" and the
     * function asserts that the error message contains a specific string.
     */
    public function testNonUniqueLogin()
    {
        // Create a new instance of the Validator class
        $validator = new Validator();

        // Set the login to a non-unique value
        $login = "existingLogin";

        // Use the Validator instance to validate the login
        $errorMsg = $validator->validateLogin($login, false);

        // Assert that the error message contains the expected string
        $this->assertStringContainsString('данний нікнейм вже зайнятий', $errorMsg);
    }

    /**
     * Test a valid login with Cyrillic characters.
     */
    public function testValidLoginWithCyrillic()
    {
        // Create a new instance of the Validator class
        $validator = new Validator();
        // Define the login with Cyrillic characters
        $login = "ксююю юю";
        // Validate the login and get the error message
        $errorMsg = $validator->validateLogin($login, true);
        // Assert that the error message is empty
        $this->assertEquals('', $errorMsg);
    }

    /**
     * Test case for validating invalid login with special character at the start with Cyrillic characters.
     */
    public function testInvalidLoginWithSpecialCharacterAtStartWithCyrillic()
    {
        // Create a new instance of the Validator class.
        $validator = new Validator();
        // Set the login string with special character at the start with Cyrillic characters.
        $login = "-кириллица123";
        // Validate the login string and get the error message.
        $errorMsg = $validator->validateLogin($login, true);
        // Assert that the error message contains the expected validation message.
        $this->assertStringContainsString('починатися з літер чи цифр', $errorMsg);
    }

    /**
     * This test case checks if an invalid login with a special character at the end,
     * along with Cyrillic characters, returns the expected error message.
     */
    public function testInvalidLoginWithSpecialCharacterAtEndWithCyrillic()
    {
        // Instantiate the Validator class
        $validator = new Validator();

        // Define the login string with Cyrillic characters and a special character at the end
        $login = "кириллица123-";

        // Call the validateLogin method with the login string and the flag indicating Cyrillic support
        $errorMsg = $validator->validateLogin($login, true);

        // Assert that the error message contains the expected substring
        $this->assertStringContainsString('закінчуватися на літеру чи цифру', $errorMsg);
    }

    /**
     * Test case for invalid login with special character in the middle with Cyrillic.
     */
    public function testInvalidLoginWithSpecialCharacterInMiddleWithCyrillic()
    {
        $validator = new Validator();
        // Set the login with Cyrillic characters and special character in the middle
        $login = "кир-`123";
        $login1 = "кир-&12333";
        $login2 = "кир-`!123";
        $login3 = "ки!р-`123";
        $login4 = "кир-%3";
        // Validate the login and get the error message
        $errorMsg = $validator->validateLogin($login, true);
        $errorMsg1 = $validator->validateLogin($login1, true);
        $errorMsg2 = $validator->validateLogin($login2, true);
        $errorMsg3 = $validator->validateLogin($login3, true);
        $errorMsg4 = $validator->validateLogin($login4, true);
        // Assert that the error message contains the expected string
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg);
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg1);
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg2);
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg3);
        $this->assertStringContainsString('нікнейм може містити літери', $errorMsg4);
    }
}