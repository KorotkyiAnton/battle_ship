<?php

namespace test;

use app\Controller;
use app\Model;
use app\Validator;
use DateTime;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{
    /**
     * Test the "addLoginToDBIfUnique" method.
     */
    public function testAddLoginToDBIfUnique()
    {
        // Create a mock object for the Model class
        $modelMock = $this->getMockBuilder(Model::class)
            ->onlyMethods(['isLoginUnique', 'getUserStatusFromQueues', 'updateOnlineStatus', 'addLoginToDB'])
            ->getMock();

        // Set up expectations for the "isLoginUnique" method
        $modelMock->expects($this->once())
            ->method('isLoginUnique')
            ->willReturn(true);

        // Create a mock object for the Validator class
        $validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up expectations for the "validateLogin" method
        $validatorMock->expects($this->once())
            ->method('validateLogin')
            ->willReturn('');

        // Create an instance of the Controller class
        $controller = new Controller($modelMock, $validatorMock);

        // Set the POST data
        $postData = ["login" => "uniqueLogin"];

        // Call the "addLoginToDBIfUnique" method
        $result = $controller->addLoginToDBIfUnique($postData);

        // Assert the expected values of the result
        $this->assertEquals(5, $result['messageId']);
        $this->assertEquals('loginRegisteredInDB', $result['messageType']);
        $this->assertInstanceOf(DateTime::class, $result['createDate']);
        $this->assertTrue($result['isWriteToDB']);
        $this->assertEquals('', $result['errMsg']);
        $this->assertEquals('uniqueLogin', $result['login']);
        $this->assertEquals(0, $result['status']);
    }

    /**
     * Test case for adding login to the database if it is not unique.
     */
    public function testAddLoginToDBIfNotUnique()
    {
        // Create a mock of the Model class with the isLoginUnique method being the only method that can be called.
        $modelMock = $this->getMockBuilder(Model::class)
            ->onlyMethods(['isLoginUnique'])
            ->getMock();

        // Set up an expectation that the isLoginUnique method will be called once and will return false.
        $modelMock->expects($this->once())
            ->method('isLoginUnique')
            ->willReturn(false);

        // Create a mock of the Validator class without calling the original constructor.
        $validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up an expectation that the validateLogin method will never be called.
        $validatorMock->expects($this->never())
            ->method('validateLogin');

        // Create an instance of the Controller class with the mocked objects.
        $controller = new Controller($modelMock, $validatorMock);

        // Define the test data.
        $postData = ["login" => "nonUniqueLogin"];

        // Call the addLoginToDBIfUnique method and store the result.
        $result = $controller->addLoginToDBIfUnique($postData);

        // Assert the expected values of the result.
        $this->assertEquals(5, $result['messageId']);
        $this->assertEquals('loginRegisteredInDB', $result['messageType']);
        $this->assertInstanceOf(DateTime::class, $result['createDate']);
        $this->assertFalse($result['isWriteToDB']);
        $this->assertStringContainsString('данний нікнейм вже зайнятий', $result['errMsg']);
        $this->assertEquals('nonUniqueLogin', $result['login']);
        $this->assertEquals(0, $result['status']);
    }
}
