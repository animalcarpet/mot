<?php

namespace DvsaMotApi\Controller\Validator;

use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use Api\Check\CheckResultExceptionTranslator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\MotTestTypeCode;

/**
 * Class CreateMotTestRequestValidator
 */
class CreateMotTestRequestValidator
{
    const FIELD_VEHICLE_ID = 'vehicleId';
    const FIELD_DVLA_VEHICLE_ID = 'dvlaVehicleId';
    const FIELD_VTS_ID = 'vehicleTestingStationId';
    const FIELD_HAS_REGISTRATION = 'hasRegistration';
    const FIELD_COLOURS_PRIMARY = 'primaryColour';
    const FIELD_COLOURS_SECONDARY = 'secondaryColour';
    const FIELD_VEHICLE_CLASS_CODE = 'vehicleClassCode';
    const FIELD_MOT_TEST_TYPE = 'motTestType';

    public static function validate(array $data)
    {
        $motTestTypeCode = ArrayUtils::tryGet($data, self::FIELD_MOT_TEST_TYPE);

        switch ($motTestTypeCode) {
            case MotTestTypeCode::RE_TEST:
                self::validateRetest($data);
                break;
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                self::validateDemo($data);
                break;
            case MotTestTypeCode::NON_MOT_TEST:
                self::validateNonMotTest($data);
                break;
            default:
                self::validateTest($data);
        }
    }

    public static function validateDemo(array $data)
    {
        $requiredKeys = [
            self::FIELD_VEHICLE_ID,
            self::FIELD_COLOURS_PRIMARY,
            self::FIELD_HAS_REGISTRATION
        ];

        self::checkRequiredKeys($data, $requiredKeys);
    }

    public static function validateNonMotTest(array $data)
    {
        $requiredKeys = [
            self::FIELD_VEHICLE_ID,
            self::FIELD_COLOURS_PRIMARY,
            self::FIELD_HAS_REGISTRATION
        ];

        self::checkRequiredKeys($data, $requiredKeys);
    }

    public static function validateRetest(array $data)
    {
        $requiredKeys = [
            self::FIELD_VEHICLE_ID,
            self::FIELD_VTS_ID,
            self::FIELD_COLOURS_PRIMARY,
            self::FIELD_HAS_REGISTRATION
        ];

        self::checkRequiredKeys($data, $requiredKeys);
    }

    public static function validateTest(array $data)
    {
        $checkResult = CheckResult::ok();

        $hasNotKey = function ($key) use (&$data) {
            return !array_key_exists($key, $data);
        };
        $addErrorFor = function ($field) use (&$checkResult) {
            $checkResult->add(CheckMessage::withError("$field is required"));
        };
        $neitherVehicleSourcesSet = $hasNotKey(self::FIELD_VEHICLE_ID) && $hasNotKey(self::FIELD_DVLA_VEHICLE_ID);
        $bothVehicleSourcesSet = !$hasNotKey(self::FIELD_VEHICLE_ID) && !$hasNotKey(self::FIELD_DVLA_VEHICLE_ID);

        if ($neitherVehicleSourcesSet || $bothVehicleSourcesSet) {
            $addErrorFor('Either vehicleId or dvsaVehicleId');
        }

        $requiredKeys = [
            self::FIELD_COLOURS_PRIMARY,
            self::FIELD_HAS_REGISTRATION,
            self::FIELD_VTS_ID,
            self::FIELD_VEHICLE_CLASS_CODE
        ];

        foreach ($requiredKeys as $key) {
            if ($hasNotKey($key)) {
                $addErrorFor($key);
            }
        }

        CheckResultExceptionTranslator::tryThrowBadRequestException($checkResult);
    }

    private static function checkRequiredKeys(array $data, array $requiredKeys)
    {
        $checkResult = CheckResult::ok();

        foreach ($requiredKeys as $k) {
            if (!array_key_exists($k, $data)) {
                $checkResult->add(CheckMessage::withError("$k is required"));
            }
        }

        CheckResultExceptionTranslator::tryThrowBadRequestException($checkResult);
    }
}
