<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\BadRequestException;
use PHPUnit_Framework_TestCase;

/**
 * Class BadRequestExceptionTest.
 */
class BadRequestExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $message = 'Invalid vehicle';
        $errorCode = 50;

        $errors = [[
            'message' => $message,
            'code' => $errorCode,
            'displayMessage' => $message,
        ]];

        $badRequestException = new BadRequestException($message, $errorCode);

        $this->assertEquals($message, $badRequestException->getMessage());
        $this->assertEquals(400, $badRequestException->getCode());
        $this->assertEquals($errors, $badRequestException->getErrors());
    }

    public function testConstructorThreeParams()
    {
        $message = 'Invalid vehicle';
        $errorCode = 50;
        $displayMessage = 'Invalid vehicle and color in bad taste';

        $errors = [[
            'message' => $message,
            'code' => $errorCode,
            'displayMessage' => $displayMessage,
        ]];

        $badRequestException = new BadRequestException($message, $errorCode, $displayMessage);

        $this->assertEquals($message, $badRequestException->getMessage());
        $this->assertEquals(400, $badRequestException->getCode());
        $this->assertEquals($errors, $badRequestException->getErrors());
    }
}
