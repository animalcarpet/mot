<?php

namespace DvsaCommon\Domain;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class BrakeTestTypeConfiguration
{
    private static $validBrakeTestCombinations = [
        VehicleClassCode::CLASS_3 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::PLATE
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ]
        ],
        VehicleClassCode::CLASS_4 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ]
        ],
        VehicleClassCode::CLASS_5 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ]
        ],
        VehicleClassCode::CLASS_7 => [
            BrakeTestTypeCode::ROLLER        => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::PLATE         => [
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ],
            BrakeTestTypeCode::DECELEROMETER => [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT
            ]
        ],
    ];

    private static $serviceBrakeLockApplicableCombinations = [
        VehicleClassCode::CLASS_3 => [
            BrakeTestTypeCode::PLATE => [
                BrakeTestTypeCode::PLATE
            ],
            BrakeTestTypeCode::ROLLER => [
                BrakeTestTypeCode::ROLLER
            ]
        ],
        VehicleClassCode::CLASS_4 => [
            BrakeTestTypeCode::PLATE => [
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT,
                BrakeTestTypeCode::PLATE
            ],
            BrakeTestTypeCode::ROLLER => [
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT,
                BrakeTestTypeCode::ROLLER
            ]
        ],
        VehicleClassCode::CLASS_5 => [
            BrakeTestTypeCode::ROLLER => [
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT,
                BrakeTestTypeCode::ROLLER
            ]
        ],
        VehicleClassCode::CLASS_7 => [
            BrakeTestTypeCode::PLATE => [
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT,
                BrakeTestTypeCode::PLATE
            ],
            BrakeTestTypeCode::ROLLER => [
                BrakeTestTypeCode::DECELEROMETER,
                BrakeTestTypeCode::GRADIENT,
                BrakeTestTypeCode::ROLLER
            ]
        ]
    ];

    public static function isValid($vehicleClass, $serviceBrakeType, $parkingBrakeType)
    {
        if (
            !isset(self::$validBrakeTestCombinations[$vehicleClass]) ||
            !isset(self::$validBrakeTestCombinations[$vehicleClass][$serviceBrakeType])
        ) {
            return false;
        }

        return in_array($parkingBrakeType, self::$validBrakeTestCombinations[$vehicleClass][$serviceBrakeType]);
    }

    public static function areServiceBrakeLocksApplicable($vehicleClass, $serviceBrakeType, $parkingBrakeType)
    {
        if (
            !isset(self::$serviceBrakeLockApplicableCombinations[$vehicleClass]) ||
            !isset(self::$serviceBrakeLockApplicableCombinations[$vehicleClass][$serviceBrakeType])
        ) {
            return false;
        }

        return in_array($parkingBrakeType, self::$serviceBrakeLockApplicableCombinations[$vehicleClass][$serviceBrakeType]);
    }
}
