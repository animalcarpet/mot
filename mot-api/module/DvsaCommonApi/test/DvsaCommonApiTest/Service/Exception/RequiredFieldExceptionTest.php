<?php

namespace DvsaCommonApiTest\Service\Exception;

use DvsaCommonApi\Service\Exception\RequiredFieldException;
use PHPUnit_Framework_TestCase;

/**
 * Class RequiredFieldExceptionTest.
 */
class RequiredFieldExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $exceptionMessage = RequiredFieldException::MESSAGE;
        $missingFieldname = 'username';
        $message = "$missingFieldname is required";
        $errors = [[
            'message' => $message,
            'code' => RequiredFieldException::ERROR_CODE_REQUIRED,
            'displayMessage' => $message,
            'field' => $missingFieldname,
        ]];

        $requiredFieldException = new RequiredFieldException([$missingFieldname]);

        $this->assertEquals($exceptionMessage, $requiredFieldException->getMessage());
        $this->assertEquals(400, $requiredFieldException->getCode());
        $this->assertEquals($errors, $requiredFieldException->getErrors());
    }

    public function testCheckForRequiredFieldsNotEmpty()
    {
        $data = [
            'field1' => null,
            'field2' => '',
        ];

        try {
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty(['field1', 'field2'], $data);
        } catch (RequiredFieldException $expected) {
            $errors = $expected->getErrors();
            $this->assertEquals(2, count($errors));
            $this->assertEquals('field1 is required', $errors[0]['message']);
            $this->assertEquals('field2 is required', $errors[1]['message']);
            $this->assertEquals('field1', $errors[0]['field']);
            $this->assertEquals('field2', $errors[1]['field']);

            return null;
        }

        $this->fail('An expected exception has not been raised.');
    }
}
