<?php

namespace PersonApiTest\Service\Validator;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use PersonApi\Service\Validator\PersonalAuthorisationForMotTestingValidator;

/**
 * unit tests for PersonalAuthorisationForMotTestingValidator.
 */
class PersonalAuthorisationForMotTestingValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    public function testValidateShouldBeOk()
    {
        $validator = new PersonalAuthorisationForMotTestingValidator();

        $data = [
            'result' => AuthorisationForTestingMotStatusCode::QUALIFIED,
            'group' => 1,
        ];
        $validator->validate($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateInvalidGroupOfVehicleShouldThrowBadRequestException()
    {
        $validator = new PersonalAuthorisationForMotTestingValidator();

        $data = [
            'result' => AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            'group' => 3,
        ];
        $validator->validate($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateInvalidStatusShouldThrowBadRequestException()
    {
        $validator = new PersonalAuthorisationForMotTestingValidator();

        $data = [
            'result' => 'eriuhge',
            'group' => 3,
        ];
        $validator->validate($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateEmptyInputShouldThrowBadRequestException()
    {
        $validator = new PersonalAuthorisationForMotTestingValidator();

        $data = [];
        $validator->validate($data);
    }
}
