<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\NotFoundException;
use PHPUnit_Framework_TestCase;

/**
 * Class NotFoundExceptionTest.
 */
class NotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $notFoundException = new NotFoundException('User', 'tester1');
        $message = 'User tester1 not found';

        $expectedErrors = [[
            'message' => $message,
            'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
            'displayMessage' => $message,
        ]];

        $this->assertEquals($message, $notFoundException->getMessage());
        $this->assertEquals(404, $notFoundException->getCode());
        $this->assertEquals($expectedErrors, $notFoundException->getErrors());
    }

    public function testConstructorWithDefaults()
    {
        $notFoundException = new NotFoundException('User');
        $message = 'User not found';

        $expectedErrors = [[
            'message' => $message,
            'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
            'displayMessage' => $message,
        ]];

        $this->assertEquals($message, $notFoundException->getMessage());
        $this->assertEquals(404, $notFoundException->getCode());
        $this->assertEquals($expectedErrors, $notFoundException->getErrors());
    }

    public function testConstructorWithSpecificMessage()
    {
        $specificMessage = 'All is lost';
        $notFoundException = new NotFoundException($specificMessage, null, false);

        $expectedErrors = [[
            'message' => $specificMessage,
            'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
            'displayMessage' => $specificMessage,
        ]];

        $this->assertEquals($specificMessage, $notFoundException->getMessage());
        $this->assertEquals(404, $notFoundException->getCode());
        $this->assertEquals($expectedErrors, $notFoundException->getErrors());
    }
}
