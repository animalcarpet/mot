<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaEntities\Entity\Vehicle;

/**
 * Class BrakeTestResultClass3TestData.
 */
class BrakeTestResultClass3TestData
{
    const DATE_CLASS_3_PRE_1968 = '1967-12-31';
    const DATE_CLASS_3_POST_1968 = '1968-01-01';

    public static function class3TwoServiceBrakeTestData()
    {
        return [
            [
                ['desc' => 'RxRxR, Test pass on efficiencies class 3',
                 'input' => [
                     'vehicleClass' => VehicleClassCode::CLASS_3,
                     'isSingleLine' => false,
                     'isSingleInFront' => false,
                     'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 500],
                     'serviceBrake1Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 70, 'off' => 75]],
                         'single' => ['effort' => 65, 'lock' => false],
                     ],
                     'serviceBrake2Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 68, 'off' => 72]],
                         'single' => ['effort' => 70, 'lock' => false],
                     ],
                     'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                          'efforts' => ['near' => 150, 'off' => 150], ],
                 ],
                 'output' => [
                     'efficiency' => ['serviceBrake1' => 42, 'serviceBrake2' => 42, 'parkingBrake' => 60],
                     'imbalanceServiceBrake1' => ['axle1' => 7],
                     'imbalanceServiceBrake2' => ['axle1' => 6],
                     'passes' => ['serviceBrake1Efficiency' => true,
                                                  'serviceBrake2Efficiency' => true,
                                                  'parkingBrakeEfficiency' => true,
                                                  'imbalanceServiceBrake1' => true,
                                                  'imbalanceServiceBrake2' => true,
                                                  'general' => true, ],
                 ], ],
            ],
            [
                ['desc' => 'RxRxR, Test pass on locks class 3, fails on parking brake',
                 'input' => [
                     'vehicleClass' => VehicleClassCode::CLASS_3,
                     'isSingleLine' => false,
                     'isSingleInFront' => false,
                     'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 500],
                     'serviceBrake1Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 30, 'off' => 30], 'locks' => ['off']],
                         'single' => ['effort' => 40, 'lock' => true],
                     ],
                     'serviceBrake2Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 30, 'off' => 30], 'locks' => ['near', 'off']],
                         'single' => ['effort' => 30, 'lock' => false],
                     ],
                     'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                          'efforts' => ['near' => 10, 'off' => 10], ],
                 ],
                 'output' => [
                     'efficiency' => ['serviceBrake1' => 20, 'serviceBrake2' => 18, 'parkingBrake' => 4],
                     'imbalanceServiceBrake1' => ['axle1' => 0],
                     'imbalanceServiceBrake2' => ['axle1' => 0],
                     'passes' => ['serviceBrake1Efficiency' => true,
                                                  'serviceBrake2Efficiency' => true,
                                                  'parkingBrakeEfficiency' => false,
                                                  'imbalanceServiceBrake1' => true,
                                                  'imbalanceServiceBrake2' => true,
                                                  'general' => false, ],
                 ], ],
            ],
            [
                ['desc' => 'RxRxR, Test pass on efficiencies class 3',
                 'input' => [
                     'vehicleClass' => VehicleClassCode::CLASS_3,
                     'isSingleLine' => true,
                     'isSingleInFront' => false,
                     'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 500],
                     'serviceBrake1Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 30, 'off' => 40]],
                         'single' => ['effort' => 55, 'lock' => false],
                     ],
                     'serviceBrake2Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 50, 'off' => 50]],
                         'single' => ['effort' => 50, 'lock' => false],
                     ],
                     'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                          'efforts' => ['near' => 60, 'off' => 65], ],
                 ],
                 'output' => [
                     'efficiency' => ['serviceBrake1' => 25, 'serviceBrake2' => 30, 'parkingBrake' => 25],
                     'imbalanceServiceBrake1' => ['axle1' => 25],
                     'imbalanceServiceBrake2' => ['axle1' => 0],
                     'passes' => ['serviceBrake1Efficiency' => true,
                                                  'serviceBrake2Efficiency' => true,
                                                  'parkingBrakeEfficiency' => true,
                                                  'imbalanceServiceBrake1' => true,
                                                  'imbalanceServiceBrake2' => true,
                                                  'general' => true, ],
                 ], ],
            ],
            [
                ['desc' => 'RxRxR, Test fails one control below 25, imbalance counted',
                 'input' => [
                     'vehicleClass' => VehicleClassCode::CLASS_3,
                     'isSingleLine' => true,
                     'isSingleInFront' => false,
                     'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 500],
                     'serviceBrake1Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 30, 'off' => 35]],
                         'single' => ['effort' => 55, 'lock' => false],
                     ],
                     'serviceBrake2Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle1' => ['efforts' => ['near' => 50, 'off' => 50]],
                         'single' => ['effort' => 50, 'lock' => false],
                     ],
                     'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                          'efforts' => ['near' => 40, 'off' => 40], ],
                 ],
                 'output' => [
                     'efficiency' => ['serviceBrake1' => 24, 'serviceBrake2' => 30, 'parkingBrake' => 16],
                     'imbalanceServiceBrake1' => ['axle1' => 15],
                     'imbalanceServiceBrake2' => ['axle1' => 0],
                     'passes' => ['serviceBrake1Efficiency' => false,
                                                  'serviceBrake2Efficiency' => true,
                                                  'parkingBrakeEfficiency' => false,
                                                  'imbalanceServiceBrake1' => true,
                                                  'imbalanceServiceBrake2' => true,
                                                  'general' => false, ],
                 ], ],
            ],
            [
                ['desc' => 'RxR, Test fail on both controls below 25 class 3, imbalance not counted',
                 'input' => [
                     'vehicleClass' => VehicleClassCode::CLASS_3,
                     'isSingleLine' => false,
                     'isSingleInFront' => true,
                     'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 500],
                     'serviceBrake1Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle2' => ['efforts' => ['near' => 40, 'off' => 40]],
                         'single' => ['effort' => 40, 'lock' => false],
                     ],
                     'serviceBrake2Test' => [
                         'type' => BrakeTestTypeCode::ROLLER,
                         'axle2' => ['efforts' => ['near' => 20, 'off' => 50]],
                         'single' => ['effort' => 76, 'lock' => false],
                     ],
                     'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                          'efforts' => ['near' => 40, 'off' => 40], ],
                 ],
                 'output' => [
                     'efficiency' => ['serviceBrake1' => 24, 'serviceBrake2' => 29, 'parkingBrake' => 16],
                     'imbalanceServiceBrake1' => ['axle1' => null],
                     'imbalanceServiceBrake2' => ['axle2' => 60],
                     'passes' => ['serviceBrake1Efficiency' => false,
                                                  'serviceBrake2Efficiency' => false,
                                                  'parkingBrakeEfficiency' => true,
                                                  'imbalanceServiceBrake1' => null,
                                                  'imbalanceServiceBrake2' => null,
                                                  'general' => false, ],
                 ], ],
            ],
            [[
                 'desc' => 'Decelerometer class 3 double service brake',
                 'input' => [
                         'vehicleClass' => Vehicle::VEHICLE_CLASS_3,
                         'isSingleLine' => false,
                         'serviceBrake1Test' => [
                             'type' => BrakeTestTypeCode::DECELEROMETER,
                             'serviceBrake1Efficiency' => 30,
                         ],
                         'serviceBrake2Test' => [
                             'type' => BrakeTestTypeCode::DECELEROMETER,
                             'serviceBrake2Efficiency' => 30,
                         ],
                         'parkingBrakeTest' => [
                             'type' => BrakeTestTypeCode::GRADIENT,
                             'parkingBrakeEfficiencyPass' => true,
                         ],
                     ],
                 'output' => [
                         'passes' => [
                            'serviceBrake1Efficiency' => true,
                            'serviceBrake2Efficiency' => true,
                            'parkingBrakeEfficiency' => true,
                            'general' => true,
                         ],
                     ],
             ]],
        ];
    }

    public static function class3SingleServiceBrakeTestData()
    {
        return [
            [['desc' => 'RxR, Test pass on efficiencies class 3 after 1968',
              'input' => [
                  'vehicleClass' => VehicleClassCode::CLASS_3,
                  'isSingleLine' => false,
                  'vehicleFirstUsed' => self::DATE_CLASS_3_POST_1968,
                  'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                  'serviceBrake1Test' => [
                      'type' => BrakeTestTypeCode::ROLLER,
                      'axle1' => ['efforts' => ['near' => 200, 'off' => 200]],
                      'single' => ['effort' => 100, 'lock' => false],
                  ],
                  'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                       'efforts' => ['near' => 150, 'off' => 150], ],
              ],
              'output' => [
                  'efficiency' => ['serviceBrake1' => 50, 'parkingBrake' => 30],
                  'imbalanceServiceBrake1' => ['axle1' => 0],
                  'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                               'imbalanceServiceBrake1' => true, 'general' => true, ],
              ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies class 3 after 1968, parking brake single',
              'input' => [
                  'vehicleClass' => VehicleClassCode::CLASS_3,
                  'isSingleLine' => false,
                  'vehicleFirstUsed' => self::DATE_CLASS_3_POST_1968,
                  'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                  'serviceBrake1Test' => [
                      'type' => BrakeTestTypeCode::ROLLER,
                      'axle1' => ['efforts' => ['near' => 200, 'off' => 200]],
                      'single' => ['effort' => 95, 'lock' => false],
                  ],
                  'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                       'efforts' => ['single' => 300], ],
              ],
              'output' => [
                  'efficiency' => ['serviceBrake1' => 49, 'parkingBrake' => 30],
                  'imbalanceServiceBrake1' => ['axle1' => 0],
                  'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => true,
                                               'imbalanceServiceBrake1' => true, 'general' => false, ],
              ], ],
            ],
            [['desc' => 'RxR, Test pass on efficiencies class 3 before 1968',
              'input' => [
                  'vehicleClass' => VehicleClassCode::CLASS_3,
                  'isSingleLine' => false,
                  'vehicleFirstUsed' => self::DATE_CLASS_3_PRE_1968,
                  'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                  'serviceBrake1Test' => [
                      'type' => BrakeTestTypeCode::ROLLER,
                      'axle1' => ['efforts' => ['near' => 133, 'off' => 133]],
                      'single' => ['effort' => 134, 'lock' => false],
                  ],
                  'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                       'efforts' => ['near' => 150, 'off' => 150], ],
              ],
              'output' => [
                  'efficiency' => ['serviceBrake1' => 40, 'parkingBrake' => 30],
                  'imbalanceServiceBrake1' => ['axle1' => 0],
                  'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                               'imbalanceServiceBrake1' => true, 'general' => true, ],
              ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies class 3 before 1968, parking brake on single lock',
              'input' => [
                  'vehicleClass' => VehicleClassCode::CLASS_3,
                  'isSingleLine' => false,
                  'vehicleFirstUsed' => self::DATE_CLASS_3_PRE_1968,
                  'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                  'serviceBrake1Test' => [
                      'type' => BrakeTestTypeCode::ROLLER,
                      'axle1' => ['efforts' => ['near' => 133, 'off' => 127]],
                      'single' => ['effort' => 134, 'lock' => false],
                  ],
                  'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                       'efforts' => ['single' => 150], 'locks' => ['single' => true], ],
              ],
              'output' => [
                  'efficiency' => ['serviceBrake1' => 39, 'parkingBrake' => 15],
                  'imbalanceServiceBrake1' => ['axle1' => 5],
                  'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => true,
                                               'imbalanceServiceBrake1' => true, 'general' => false, ],
              ], ],
            ],
            [['desc' => 'RxR, Test fail on efficiencies class 3 before 1968, parking brake on single lock',
              'input' => [
                  'vehicleClass' => VehicleClassCode::CLASS_3,
                  'isSingleLine' => false,
                  'vehicleFirstUsed' => self::DATE_CLASS_3_PRE_1968,
                  'weight' => ['type' => WeightSourceCode::PRESENTED, 'value' => 1000],
                  'serviceBrake1Test' => [
                      'type' => BrakeTestTypeCode::ROLLER,
                      'axle1' => ['efforts' => ['near' => 133, 'off' => 127]],
                      'single' => ['effort' => 134, 'lock' => false],
                  ],
                  'parkingBrakeTest' => ['type' => BrakeTestTypeCode::ROLLER,
                       'efforts' => ['single' => 150], 'locks' => ['single' => true], ],
              ],
              'output' => [
                  'efficiency' => ['serviceBrake1' => 39, 'parkingBrake' => 15],
                  'imbalanceServiceBrake1' => ['axle1' => 5],
                  'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => true,
                                               'imbalanceServiceBrake1' => true, 'general' => false, ],
              ], ],
            ],
        ];
    }
}
