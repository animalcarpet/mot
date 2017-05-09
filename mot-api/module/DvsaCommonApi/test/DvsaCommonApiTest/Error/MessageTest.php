<?php

namespace DvsaCommonApiTest\Error;

use DvsaCommonApi\Error\Message as ErrorMessage;
use PHPUnit_Framework_TestCase;

/**
 * Class MessageTest.
 */
class MessageTest extends PHPUnit_Framework_TestCase
{
    const TEST_MESSAGE = 'A unit test display message';
    const TEST_CODE = 42;

    public function testGetDisplayMessage()
    {
        $errorMessage = new ErrorMessage(
            self::TEST_MESSAGE,
            self::TEST_CODE
        );

        $this->assertEquals(self::TEST_MESSAGE, $errorMessage->getDisplayMessage());
    }

    public function testGetErrorCode()
    {
        $errorMessage = new ErrorMessage(
            self::TEST_MESSAGE,
            self::TEST_CODE
        );

        $this->assertEquals(self::TEST_CODE, $errorMessage->getErrorCode());
    }

    public function testGetFieldDataStructure()
    {
        $testStructure = ['cat' => ['colour' => null]];

        $errorMessage = new ErrorMessage(
            self::TEST_MESSAGE,
            self::TEST_CODE,
            $testStructure
        );

        $this->assertEquals($testStructure, $errorMessage->getFieldDataStructure());
    }

    public function testGetMessage()
    {
        $errorMessage = new ErrorMessage(
            self::TEST_MESSAGE,
            self::TEST_CODE
        );

        $this->assertEquals(self::TEST_MESSAGE, $errorMessage->getMessage());
    }

    public function testGetMessage2()
    {
        $differentDisplayMessage = 'Different display message';

        $errorMessage = new ErrorMessage(
            $differentDisplayMessage,
            self::TEST_CODE,
            self::TEST_MESSAGE
        );

        $this->assertEquals($differentDisplayMessage, $errorMessage->getMessage());
    }
}
