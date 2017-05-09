<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;

/**
 * Class BrakeTestResultClassRollerTestData.
 */
class BrakeTestResultClassRollerTestData
{
    const DATE_CLASS_4_PRE_2010_SEP = '2010-08-31';
    const DATE_CLASS_4_POST_2010_SEP = '2010-09-01';

    public static function allRollerTestData()
    {
        return [

            [['desc' => 'RxR, Test pass on efficiencies class 4', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'vehicleFirstUsed' => self::DATE_CLASS_4_PRE_2010_SEP, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 21], 'imbalanceServiceBrake1' => ['axle1' => 15], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies and imbalance class 4', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60]], 'axle2' => ['efforts' => ['near' => 55, 'off' => 85], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 36], 'passes' => ['serviceBrake1Efficiency' => false,
                                                   'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => false, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on locks and imbalance class 4', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 65, 'off' => 75], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 14], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 21], 'imbalanceServiceBrake1' => ['axle1' => 15], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60]], 'axle2' => ['efforts' => ['near' => 55, 'off' => 85], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 36], 'passes' => ['serviceBrake1Efficiency' => false,
                                                   'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => false, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on locks and imbalance class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 65, 'off' => 75], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 14], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on parking brake single line class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 21], 'imbalanceServiceBrake1' => ['axle1' => 15], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => true, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on parking brake above 25 for single line class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 175, 'off' => 175]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 25], 'imbalanceServiceBrake1' => ['axle1' => 15], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on three axles class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']], 'axle3' => ['efforts' => ['near' => 100, 'off' => 120]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 175, 'off' => 175]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 61, 'parkingBrake' => 23], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle3' => 17], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on three axles with locks class 5', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 50],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 6], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                     'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on three axles with only 3 locks', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_5, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50]], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 50, 'nearSecondary' => 30, 'offSecondary' => 10],
                         'locks' => ['off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 9], 'imbalanceSecondaryParkingBrake' => 67, 'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => false,
                                     'parkingBrakeImbalance' => false,
                                     'imbalanceServiceBrake1' => true, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => true], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 21], 'imbalanceServiceBrake1' => ['axle1' => 15], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies and imbalance class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => false], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 55, 'off' => 85]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 36], 'passes' => ['serviceBrake1Efficiency' => false,
                                                   'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => false, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on front locked, back at least 100 class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => true], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 105, 'off' => 115]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 300, 'off' => 300]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 25, 'parkingBrake' => 43], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 9], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'Test fail on front locked, back at least 100 class 7 weight laden', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => true], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 105, 'off' => 115]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 300, 'off' => 300]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 25, 'parkingBrake' => 43], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 9], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on front locked, back at least 100, three axles class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => true], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 3, 'off' => 3], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 105, 'off' => 100]], 'axle3' => ['efforts' => ['near' => 120, 'off' => 105]],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 300, 'off' => 300]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 31, 'parkingBrake' => 43], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 5, 'axle3' => 13], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on locks and imbalance class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370, 'unladen' => true], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 70, 'off' => 60], 'locks' => ['near', 'off']], 'axle2' => ['efforts' => ['near' => 65, 'off' => 75], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 100, 'off' => 100],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 19, 'parkingBrake' => 14], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 14], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on parking brake single line class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 21], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => true, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on parking brake above 25 for single line class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1370], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 175, 'off' => 175]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 51, 'parkingBrake' => 25], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on three axles class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 115, 'off' => 135]], 'axle2' => ['efforts' => ['near' => 225, 'off' => 225], 'locks' => ['off']], 'axle3' => ['efforts' => ['near' => 100, 'off' => 120], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 175, 'off' => 175]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 61, 'parkingBrake' => 23], 'imbalanceServiceBrake1' => ['axle1' => 15, 'axle2' => 0, 'axle3' => 17], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on three axles with locks class 7', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 50],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 6], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on three axles with only 3 locks', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 50, 'off' => 50]], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 50], 'locks' => ['off']],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 6], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0], 'passes' => ['serviceBrake1Efficiency' => false,
                                                   'parkingBrakeEfficiency' => false,
                                                   'imbalanceServiceBrake1' => true, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on single type parking brake imbalance', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 100],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 10], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0], 'imbalanceParkingBrake' => 50, 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => false,
                                                   'parkingBrakeImbalance' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on single type parking brake imbalance', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 50],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 6], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0], 'imbalanceParkingBrake' => 0, 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true,
                                                   'parkingBrake' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on double type on parking brake imbalance', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_7, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1500], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER, 'axle1' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']], 'axle2' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near', 'off']], 'axle3' => ['efforts' => ['near' => 50, 'off' => 50], 'locks' => ['near']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 50, 'off' => 100],
                         'locks' => ['near', 'off'], ],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 20, 'parkingBrake' => 10], 'imbalanceServiceBrake1' => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0], 'imbalanceParkingBrake' => null, 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true,
                                                   'parkingBrake' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 4 after 2010', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'vehicleFirstUsed' => self::DATE_CLASS_4_POST_2010_SEP, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 140, 'off' => 140]], 'axle2' => ['efforts' => ['near' => 150, 'off' => 150], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 58, 'parkingBrake' => 30], 'imbalanceServiceBrake1' => ['axle1' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 4 after 2010', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'vehicleFirstUsed' => self::DATE_CLASS_4_POST_2010_SEP, 'isSingleLine' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 140, 'off' => 140]], 'axle2' => ['efforts' => ['near' => 150, 'off' => 150], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 58, 'parkingBrake' => 30], 'imbalanceServiceBrake1' => ['axle1' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies class 4 after 2010', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'vehicleFirstUsed' => self::DATE_CLASS_4_POST_2010_SEP, 'isSingleLine' => false, 'isCommercialVehicle' => false, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 140, 'off' => 140]], 'axle2' => ['efforts' => ['near' => 145, 'off' => 145], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 57, 'parkingBrake' => 30], 'imbalanceServiceBrake1' => ['axle1' => 0], 'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => false, ],
                ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 4 after 2010 - commercial vehicle', 'input' => [
                    'vehicleClass' => VehicleClassCode::CLASS_4, 'vehicleFirstUsed' => self::DATE_CLASS_4_POST_2010_SEP, 'isSingleLine' => false, 'isCommercialVehicle' => true, 'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000], 'serviceBrake1Test' => [
                        'type' => BrakeTestTypeCode::ROLLER,
                        'axle1' => ['efforts' => ['near' => 140, 'off' => 140]], 'axle2' => ['efforts' => ['near' => 145, 'off' => 145], 'locks' => ['off']],
                    ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER, 'efforts' => ['near' => 150, 'off' => 150]],
                ], 'output' => [
                    'efficiency' => ['serviceBrake1' => 57, 'parkingBrake' => 30], 'imbalanceServiceBrake1' => ['axle1' => 0], 'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                                   'imbalanceServiceBrake1' => true, 'general' => true, ],
                ], ],
            ],
        ];
    }
}
