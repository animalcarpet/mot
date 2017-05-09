<?php

namespace DvsaMotApiTest\Service\Validator;

use DvsaMotApi\Service\Validator\DemoTestAssessmentValidator;
use DvsaCommon\Enum\VehicleClassGroupCode;

class DemoTestAssessmentValidatorTest extends \PHPUnit_Framework_TestCase
{
    const REQUIRED_FIELD_EXCEPTION = '\DvsaCommonApi\Service\Exception\RequiredFieldException';
    const BAD_REQUEST_EXCEPTION = '\DvsaCommonApi\Service\Exception\BadRequestException';

    private $validator;

    public function setUp()
    {
        $this->validator = new DemoTestAssessmentValidator();
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testValidate_whenPassInvalidData_throwsError($data, $error)
    {
        $this->setExpectedException($error);

        $this->validator->validate($data);
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testValidate_whenPassValidData_notThrowException($data)
    {
        $this->validator->validate($data);
    }

    public function invalidDataProvider()
    {
        return [
            //empty data
            [
                [],
                self::REQUIRED_FIELD_EXCEPTION,
            ],

            //FIELD_TESTER_ID is not digit
            [
                [
                    DemoTestAssessmentValidator::FIELD_TESTER_ID => '1a',
                    DemoTestAssessmentValidator::FIELD_VEHICLE_CLASS_GROUP => VehicleClassGroupCode::BIKES,
                ],
                self::BAD_REQUEST_EXCEPTION,
            ],

            //FIELD_VEHICLE_CLASS_GROUP not exists
            [
                [
                    DemoTestAssessmentValidator::FIELD_TESTER_ID => 1,
                    DemoTestAssessmentValidator::FIELD_VEHICLE_CLASS_GROUP => 'X',
                ],
                self::BAD_REQUEST_EXCEPTION,
            ],
        ];
    }

    public function validDataProvider()
    {
        return [
            [
                [
                    DemoTestAssessmentValidator::FIELD_TESTER_ID => 1,
                    DemoTestAssessmentValidator::FIELD_VEHICLE_CLASS_GROUP => VehicleClassGroupCode::BIKES,
                ],
                [
                    DemoTestAssessmentValidator::FIELD_TESTER_ID => 1,
                    DemoTestAssessmentValidator::FIELD_VEHICLE_CLASS_GROUP => VehicleClassGroupCode::CARS_ETC,
                ],
            ],
        ];
    }
}
