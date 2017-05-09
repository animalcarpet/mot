<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;

/**
 * Class BrakeTestResultClass4CalculatorImbalanceTestData.
 */
class BrakeTestResultClass4CalculatorImbalanceTestData
{
    public static function rollersImbalanceTests()
    {
        return
            [
                [['desc' => 'RxR, Axle1: imbalance gt limit, nearside lower & locked, imbalance passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 150, 'off' => 250], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => ['axle1' => 40], 'passes' => ['imbalanceServiceBrake1' => true, 'general' => true],
                    ],
                ]],
                [['desc' => 'RxR, Axle1: imbalance slightly above limit, offside lower, nearside locked, imbalance failed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 100, 'off' => 100 - BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM - 1], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => ['axle1' => BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM + 1], 'passes' => ['imbalanceServiceBrake1' => false, 'general' => false],
                    ],
                ]],
                [['desc' => 'RxR, Axle1: imbalance eq limit, offside lower, nearside locked, imbalance passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 100, 'off' => 100 - BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200 - (2 * BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM)]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => [
                            'axle1' => BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM, 'axle2' => BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM, ], 'passes' => ['imbalanceServiceBrake1' => true, 'general' => true],
                    ],
                ]],
                [['desc' => 'RxRxR, 3axles, imbalance passed axle1, axle2, axle3, overall: passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 250, 'off' => 250], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]], 'axle3' => ['efforts' => ['near' => 100, 'off' => 100 - BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => ['axle3' => 30, 'axle3Pass' => true], 'passes' => ['imbalanceServiceBrake1' => true, 'general' => true],
                    ],
                ]],
                [['desc' => 'RxR, Parking brake: imbalance eq limit, imbalance passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 100, 'off' => 100]], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => [
                                'near' => 200, 'off' => 200 - (2 * BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM), 'nearSecondary' => 200, 'offSecondary' => 200 - (2 * BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM),
                            ]],
                    ], 'output' => [
                        'imbalanceParkingBrake' => BrakeTestResultClass3AndAboveCalculator::IMBALANCE_MAXIMUM, 'passes' => ['imbalanceParkingBrake' => true, 'imbalanceSecondaryParkingBrake' => true, 'general' => true],
                    ],
                ]],
                [['desc' => 'RxR, Axle1: imbalance eq limit, offside lower & locked, imbalance passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 100, 'off' => 70], 'locks' => ['off']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                            'efforts' => ['near' => 200, 'off' => 200], ],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => ['axle1' => 30], 'passes' => ['imbalanceServiceBrake1' => true, 'general' => true],
                    ],
                ]],
                [['desc' => 'RxR, Axle1: imbalance lt limit, offside lower, both locked, imbalance passed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                        'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 100, 'off' => 80], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                    ], 'output' => [
                        'efficiency' => ['serviceBrake1' => 58, 'parkingBrake' => 40], 'imbalanceServiceBrake1' => ['axle1' => 20], 'passes' => ['serviceBrake1Efficiency' => true,
                            'parkingBrakeEfficiency' => true,
                            'imbalanceServiceBrake1' => true, 'general' => true, ],
                    ],
                ]],
                [['desc' => 'RxR, Axle1: imbalance < limit, offside lower, both locked, imbalance passed,'
                    .'Axle2: imbalance > limit, offside lower, nearside locked, imbalance failed'
                    .'general: failed', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                            'type' => BrakeTestTypeCode::ROLLER,
                            'axle1' => ['efforts' => ['near' => 100, 'off' => 80], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 120]],
                        ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100]],
                    ], 'output' => [
                        'imbalanceServiceBrake1' => ['axle1' => 20, 'axle2' => 40], 'passes' => ['imbalanceServiceBrake1' => false, 'general' => false],
                    ],
                ]],
            ];
    }

    public static function rollersAxleImbalancePassTest()
    {
        return [
            [['desc' => 'RxR, 2 axles, imbalance passed axle1+axle2, overall: passed', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 150, 'off' => 250], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 200, 'off' => 200]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                ], 'output' => [
                    'imbalanceServiceBrake1' => ['axle1' => 40, 'axle1Pass' => true, 'axle2Pass' => true], 'passes' => ['imbalanceServiceBrake1' => true, 'general' => true],
                ],
            ]],
            [['desc' => 'RxRxR, 3axles, imbalance passed axle1, failed axle2, passed axle3,  overall: failed', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 150, 'off' => 250], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 200]], 'axle3' => ['efforts' => ['near' => 100, 'off' => 100]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 200, 'off' => 200]],
                ], 'output' => [
                    'imbalanceServiceBrake1' => ['axle1' => 40, 'axle2' => 75, 'axle1Pass' => true,
                                                 'axle2Pass' => false, 'axle3Pass' => true, ], 'passes' => ['imbalanceServiceBrake1' => false, 'general' => false],
                ],
            ]],
        ];
    }
}
