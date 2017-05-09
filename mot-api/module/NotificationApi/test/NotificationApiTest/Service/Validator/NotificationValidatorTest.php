<?php

namespace NotificationApiTest\Service\Validator;

use NotificationApi\Service\Validator\NotificationValidator;
use PHPUnit_Framework_TestCase;

/**
 * Class NotificationValidatorTest.
 */
class NotificationValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var $validator \NotificationApi\Service\Validator\NotificationValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new NotificationValidator();
    }

    public function test_validation_validData_shouldBeOk()
    {
        $this->validator->validate(['recipient' => 1, 'template' => 1, 'fields' => []]);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_validation_noData_shouldThrowBadRequestException()
    {
        $this->validator->validate(null);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException */
    public function test_validation_emptyArray_shouldThrowBadRequestException()
    {
        $this->validator->validate([]);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException */
    public function test_validation_invalidData_shouldBeOk()
    {
        $this->validator->validate(['recipient' => 1, 'template' => 1]);
    }

    public function test_validationActionData_validData_shouldBeOk()
    {
        $this->validator->validateActionData(['action' => 'validAction']);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\BadRequestException */
    public function test_validateActionData_noData_shouldThrowBadRequestException()
    {
        $this->validator->validateActionData(null);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException */
    public function test_validateActionData_emptyArray_shouldThrowBadRequestException()
    {
        $this->validator->validateActionData([]);
    }
}
