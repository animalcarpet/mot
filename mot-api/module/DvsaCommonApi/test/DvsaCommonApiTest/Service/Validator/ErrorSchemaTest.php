<?php

namespace DvsaCommonApiTest\Service\Validator;

use DvsaCommonApi\Service\Validator\ErrorSchema;
use PHPUnit_Framework_TestCase;

/**
 * Class ErrorSchemaTest.
 */
class ErrorSchemaTest extends PHPUnit_Framework_TestCase
{
    public function testCanAddGlobalError()
    {
        // Given I have an error schema and an error message
        $errors = new ErrorSchema();
        $errorMessage = 'This is wrong';

        // When I add the message to errors without specifying a field
        $errors->add($errorMessage);

        // Then the message is stored as a global error
        $this->assertContains($errorMessage, $errors->getGlobal(), 'Not in the list of global errors.');

        // And it's also available in the list of all errors
        $this->assertContains($errorMessage, $errors->getAll(), 'Not in the list of all errors');
    }

    public function testCanAddErrorForSpecificField()
    {
        // Given I have an error schema, an error message and a form field
        $errors = new ErrorSchema();
        $errorMessage = 'This is wrong';
        $field = 'username';

        // When I add the message to errors for the specified field
        $errors->add($errorMessage, $field);

        // Then the message is stored as an error for the specified field
        $this->assertContains($errorMessage, $errors->getForField($field), 'Not in the list of field errors.');

        // And it's also available in the list of all errors
        $this->assertContains($errorMessage, $errors->getAll(), 'Not in the list of all errors');
    }

    public function testCountErrors()
    {
        $expectedCount = 5;

        // Given I have multiple different errors
        $error1 = 'error 1';
        $error2 = 'error 2';
        $error3 = 'error 3';
        $error4 = 'error 4';
        $error5 = 'error 5';

        $errors = new ErrorSchema();
        $field1 = 'username';
        $field2 = 'address';

        // When I add them to the error schema
        $errors->add($error1);
        $errors->add($error2);

        $errors->add($error3, $field1);
        $errors->add($error4, $field1);

        $errors->add($error5, $field2);

        // Then the schema contains proper count of them
        $this->assertEquals($expectedCount, $errors->count(), 'Wrong count of errors');
    }

    public function testNoExceptionThrownWhenNoErrors()
    {
        // Given I have an error schema without any errors
        $errors = new ErrorSchema();

        // When I try to throw it as an exception
        $errors->throwIfAny();

        // Then no exception is thrown
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testExceptionIsThrownWhenThereAreErrors()
    {
        // Given I have an error schema with errors
        $errors = new ErrorSchema();
        $errors->add('An error');

        // When I try to throw it as an exception
        $errors->throwIfAny();

        // Then the exception is thrown
    }
}
