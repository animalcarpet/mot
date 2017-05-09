<?php

namespace DvsaMotApiTest\Controller\Validator;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Controller\Validator\CreateMotTestRequestValidator;
use DvsaMotApi\Service\CreateMotTestService;

/**
 * Class CreateMotTestRequestValidatorTest.
 */
class CreateMotTestRequestValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider_mot_test_requestData
     */
    public function testValidate($data, $isError)
    {
        if ($isError) {
            $this->setExpectedException(BadRequestException::class);
        }
        CreateMotTestRequestValidator::validate($data);
    }

    /**
     * @dataProvider provider_demo_test_requestData
     */
    public function test_ValidateDemo($data, $shouldBeInvalid)
    {
        if ($shouldBeInvalid) {
            $this->setExpectedException(BadRequestException::class);
        }
        CreateMotTestRequestValidator::validateDemo($data);
    }

    /**
     * @dataProvider provider_non_mot_test_requestData
     */
    public function test_ValidateNonMot($data, $shouldBeInvalid)
    {
        if ($shouldBeInvalid) {
            $this->setExpectedException(BadRequestException::class);
        }
        CreateMotTestRequestValidator::validateDemo($data);
    }

    const FIELD_VEHICLE_ID = 'vehicleId';
    const FIELD_DVLA_VEHICLE_ID = 'dvlaVehicleId';
    const FIELD_VTS_ID = 'vehicleTestingStationId';
    const FIELD_HAS_REGISTRATION = 'hasRegistration';
    const FIELD_COLOURS_PRIMARY = 'primaryColour';
    const FIELD_COLOURS_SECONDARY = 'secondaryColour';
    const FIELD_VEHICLE_CLASS_CODE = 'vehicleClassCode';

    /**
     * @return array
     */
    public static function provider_mot_test_requestData()
    {
        return [
            [ // positive example, vehicleId is set
                [
                    'vehicleId' => 1,
                    'vehicleTestingStationId' => 1,
                    'hasRegistration' => true,
                    'primaryColour' => 1,
                    'secondaryColour' => 2,
                    'vehicleClassCode' => VehicleClassCode::CLASS_1,
                ], false,
            ],
            [ // positive example, dvlaVehicleId is set
                [
                    'dvlaVehicleId' => 1,
                    'vehicleTestingStationId' => 1,
                    'hasRegistration' => true,
                    'primaryColour' => 1,
                    'secondaryColour' => 2,
                    'vehicleClassCode' => VehicleClassCode::CLASS_1,
                ], false,
            ],
            [ // negative, neither vehicleId nor dvlaVehicleId is set
                [
                    'vehicleTestingStationId' => 1,
                    'hasRegistration' => true,
                    'primaryColour' => 1,
                    'secondaryColour' => 2,
                    'vehicleClassCode' => VehicleClassCode::CLASS_1,
                ], true,
            ],
            [ // one of other required properties is not set
                [
                    'vehicleTestingStationId' => 1,
                    'hasRegistration' => true,
                    'secondaryColour' => 2,
                    'vehicleClassCode' => VehicleClassCode::CLASS_1,
                ], true,
            ],
        ];
    }

    public static function provider_demo_test_requestData()
    {
        return [
            [ // data OK
                [
                    CreateMotTestService::FIELD_COLOURS_PRIMARY => 1,
                    CreateMotTestService::FIELD_VEHICLE_ID => 1,
                    CreateMotTestService::FIELD_HAS_REGISTRATION => 1,
                ],
                false,
            ],
            [ // primary colour missing
                [
                    CreateMotTestService::FIELD_VEHICLE_ID => 1,
                    CreateMotTestService::FIELD_HAS_REGISTRATION => 1,
                ],
                true,
            ],
            [ // vehicle Id missing
                [
                    CreateMotTestService::FIELD_COLOURS_PRIMARY => 1,
                    CreateMotTestService::FIELD_HAS_REGISTRATION => 1,
                ],
                true,
            ],
            [ // has registration missing
                [
                    CreateMotTestService::FIELD_COLOURS_PRIMARY => 1,
                    CreateMotTestService::FIELD_VEHICLE_ID => 1,
                ],
                true,
            ],
        ];
    }

    public static function provider_non_mot_test_requestData()
    {
        // currently same restriction as demo test

        return self::provider_demo_test_requestData();
    }
}
