<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;
use DvsaCommonApi\Error\Message as ErrorMessage;
use PHPUnit_Framework_TestCase;

/**
 * Class BadRequestExceptionWithMultipleErrorsTest.
 */
class BadRequestExceptionWithMultipleErrorsTest extends PHPUnit_Framework_TestCase
{
    const TEST_MESSAGE = 'Invalid parameter';
    const TEST_MESSAGE_2 = 'Cat is not a colour';
    const TEST_MAIN_ERROR_MESSAGE = 'There were errors';
    const TEST_MAIN_ERROR_MESSAGE_2 = 'The cat parameter was fishy';
    const TEST_CODE = 42;

    public function testConstructor()
    {
        $mainErrors = $this->getMainErrors();
        $formFieldErrors = $this->getFormFieldErrors();
        $exception = new BadRequestExceptionWithMultipleErrors($mainErrors, $formFieldErrors);

        $this->assertEquals(400, $exception->getCode());
        $this->assertTrue(is_array($exception->getErrors()));
        $this->assertNotEmpty($exception->getErrors());
        $this->assertTrue(is_array($exception->getErrorData()));
        $this->assertNotEmpty($exception->getErrorData());

        // Check that the errorData "leaves" point to valid error messages
        $errorData = $exception->getErrorData();
        $errors = $exception->getErrors();

        array_walk_recursive(
            $errorData,
            function ($item, $key, $errors) {
                $this->assertArrayHasKey($item, $errors);
            }, $errors
        );
    }

    private function getFormFieldErrors()
    {
        $errors = [];
        $errors[] = new ErrorMessage(self::TEST_MESSAGE, self::TEST_CODE, ['postParameter' => null]);
        $errors[] = new ErrorMessage(self::TEST_MESSAGE_2, self::TEST_CODE, ['catColour' => null]);

        return $errors;
    }

    private function getMainErrors()
    {
        $errors = [];
        $errors[] = new ErrorMessage(self::TEST_MAIN_ERROR_MESSAGE, self::TEST_CODE);
        $errors[] = new ErrorMessage(self::TEST_MAIN_ERROR_MESSAGE_2, self::TEST_CODE);

        return $errors;
    }
}
