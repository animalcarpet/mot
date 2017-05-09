<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\ForbiddenException;
use PHPUnit_Framework_TestCase;

/**
 * Class ForbiddenExceptionTest.
 */
class ForbiddenExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $message = 'You do not have permission to test a Monster Truck';
        $forbiddenException = new ForbiddenException($message);

        $expectedErrors = [[
            'message' => $message,
            'code' => 403,
            'displayMessage' => $message,
        ]];

        $this->assertEquals($message, $forbiddenException->getMessage());
        $this->assertEquals(403, $forbiddenException->getCode());
        $this->assertEquals($expectedErrors, $forbiddenException->getErrors());
    }
}
