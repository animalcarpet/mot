<?php

namespace DvsaCommon\Domain;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class BrakeTestTypeConfiguration
{
    private static $brakeTestCombinations = [
        VehicleClassCode::CLASS_3 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => true,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::GRADIENT      => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
        ],
        VehicleClassCode::CLASS_4 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => true,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::GRADIENT      => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
        ],
        VehicleClassCode::CLASS_5 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::GRADIENT      => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
        ],
        VehicleClassCode::CLASS_7 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => true,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER        => true,
                BrakeTestTypeCode::PLATE         => true,
                BrakeTestTypeCode::DECELEROMETER => true,
                BrakeTestTypeCode::GRADIENT      => true
            ],
            BrakeTestTypeCode::GRADIENT      => [
                BrakeTestTypeCode::ROLLER        => false,
                BrakeTestTypeCode::PLATE         => false,
                BrakeTestTypeCode::DECELEROMETER => false,
                BrakeTestTypeCode::GRADIENT      => false
            ],
        ],
    ];

    public static function isValid($vehicleClass, $serviceBrakeType, $parkingBrakeType)
    {
        if (
            isset(self::$brakeTestCombinations[$vehicleClass]) &&
            isset(self::$brakeTestCombinations[$vehicleClass][$serviceBrakeType]) &&
            isset(self::$brakeTestCombinations[$vehicleClass][$serviceBrakeType][$parkingBrakeType])
        ) {
            return self::$brakeTestCombinations[$vehicleClass][$serviceBrakeType][$parkingBrakeType];
        }

        return false;
    }

    public static function areServiceBrakeLocksApplicable($vehicleClass, $serviceBrakeType, $parkingBrakeType)
    {
        if (!self::isValid($vehicleClass, $serviceBrakeType, $parkingBrakeType)) {
            return false;
        }

        if (
            in_array($vehicleClass, [VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_7]) &&
            in_array($serviceBrakeType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE]) &&
            in_array($parkingBrakeType, [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT])
        ) {
            return true;
        }

        if (
            in_array($vehicleClass, [VehicleClassCode::CLASS_5]) &&
            in_array($serviceBrakeType, [BrakeTestTypeCode::ROLLER]) &&
            in_array($parkingBrakeType, [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT])
        ) {
            return true;
        }

        return in_array( // any vehicle class, any service brake type
            $parkingBrakeType,
            [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE]
        );
    }
}
