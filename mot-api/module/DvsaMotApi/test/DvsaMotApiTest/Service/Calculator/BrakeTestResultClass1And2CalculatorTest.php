<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass1And2Calculator;

/**
 * Class BrakeTestResultClass1And2CalculatorTest
 */
class BrakeTestResultClass1And2CalculatorTest extends \PHPUnit_Framework_TestCase
{
    const DATE_BIKE_ONE_CONTROL_ALLOWED = '1926-12-31';
    const DATE_BIKE_TWO_CONTROLS_REQUIRED = '1927-01-01';

    /**
     * This is the data provided for the test
     *
     * @dataProvider brakeTestResultItemsRollerFloorPlate
     */
    public function testBrakeTestResultCalculatorForClasses1And2RollerFloorPlate($brakeTestResultItem)
    {
        $weights = $brakeTestResultItem['weights'];
        $efforts = $brakeTestResultItem['efforts'];
        $locks = $brakeTestResultItem['locks'];
        $efficiency = $brakeTestResultItem['efficiency'];
        $results = $brakeTestResultItem['results'];
        $message = $brakeTestResultItem['message'];
        $rfrs = $brakeTestResultItem['rfrs'];
        $lockPercentage = isset($brakeTestResultItem['lockPercentage']) ? $brakeTestResultItem['lockPercentage'] : [];

        $firstUsedDate = new \DateTime(
            isset($brakeTestResultItem['oldBike'])
            ? self::DATE_BIKE_ONE_CONTROL_ALLOWED : self::DATE_BIKE_TWO_CONTROLS_REQUIRED
        );
        $brakeTestResult = new BrakeTestResultClass12();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::type($brakeTestResultItem['type']))
            ->setVehicleWeightFront($weights[0])
            ->setVehicleWeightRear($weights[1])
            ->setSidecarWeight($weights[2])
            ->setRiderWeight($weights[3])
            ->setControl1EffortFront($efforts[0])
            ->setControl1EffortRear($efforts[1])
            ->setControl1EffortSidecar($efforts[2])
            ->setControl2EffortFront($efforts[3])
            ->setControl2EffortRear($efforts[4])
            ->setControl2EffortSidecar($efforts[5])
            ->setControl1LockFront($locks[0])
            ->setControl1LockRear($locks[1])
            ->setControl2LockFront($locks[2])
            ->setControl2LockRear($locks[3]);
        $brakeTestResultCalculator = new BrakeTestResultClass1And2Calculator();
        $brakeTestResultCalculator->calculateBrakeTestResult($brakeTestResult, $firstUsedDate);
        $this->assertBrakeTestResults(
            $brakeTestResult, $brakeTestResultCalculator, $message, $results, $rfrs, $efficiency, $lockPercentage
        );
    }

    protected function assertBrakeTestResults(
        BrakeTestResultClass12 $brakeTestResult,
        BrakeTestResultClass1And2Calculator $brakeTestResultCalculator,
        $message,
        $results,
        $rfrs,
        $efficiencies = null,
        $lockPercentage = null
    ) {
        if ($efficiencies) {
            $this->assertEquals(
                $efficiencies[0], $brakeTestResult->getControl1BrakeEfficiency(), 'Efficiency 1 - ' . $message
            );
            $this->assertEquals(
                $efficiencies[1], $brakeTestResult->getControl2BrakeEfficiency(), 'Efficiency 2 - ' . $message
            );
        }
        $this->assertSame($results[0], $brakeTestResult->getControl1EfficiencyPass(), 'Pass 1 - ' . $message);
        $this->assertSame($results[1], $brakeTestResult->getControl2EfficiencyPass(), 'Pass 2 - ' . $message);
        $this->assertSame($results[2], $brakeTestResult->getGeneralPass(), 'Pass - ' . $message);
        if(!empty($lockPercentage)){
            $ctrl1Lock = $brakeTestResultCalculator->calculateControl1PercentLocked($brakeTestResult);
            $ctrl2Lock = $brakeTestResultCalculator->calculateControl2PercentLocked($brakeTestResult);
            $this->assertEquals($ctrl1Lock, $lockPercentage[0]);
            $this->assertEquals($ctrl2Lock, $lockPercentage[2]);
        }
        $this->assertRfrs($brakeTestResultCalculator, $brakeTestResult, $rfrs, $message);
    }

    public static function brakeTestResultItemsRollerFloorPlate()
    {
        $floorDataProvider = [
            [
                [
                    'type'       => BrakeTestTypeCode::FLOOR,
                    'weights'    => [185, 135, 0, 80],
                    'efforts'    => [20, null, null, 20, null, null],
                    'locks'      => [true, false, true, false],
                    'efficiency' => [5, 5],
                    'results'    => [true, true, true],
                    'rfrs'       => [false, false, false],
                    'message'    => 'Test passing with both locks',
                    'lockPercentage' => [100, 0, 100, 0]
                ]
            ],
            [
                [
                    'type'       => BrakeTestTypeCode::FLOOR,
                    'weights'    => [185, 135, 0, 80],
                    'efforts'    => [20, null, null, 20, null, null],
                    'locks'      => [false, false, true, false],
                    'efficiency' => [5, 5],
                    'results'    => [false, true, false],
                    'rfrs'       => [false, false, true],
                    'message'    => 'Test failing with secondary control lock',
                    'lockPercentage' => [0, 0, 100, 0]
                ]
            ],
            [
                [
                    'type'       => BrakeTestTypeCode::FLOOR,
                    'weights'    => [185, 135, 0, 80],
                    'efforts'    => [20, null, null, 20, null, null],
                    'locks'      => [true, false, false, false],
                    'efficiency' => [5, 5],
                    'results'    => [true, false, false],
                    'rfrs'       => [false, false, true],
                    'message'    => 'Test failing with primary control lock',
                    'lockPercentage' => [100, 0, 0, 0]
                ]
            ],
            [
                [
                    'type'       => BrakeTestTypeCode::FLOOR,
                    'weights'    => [185, 135, 0, 80],
                    'efforts'    => [20, null, null, 20, null, null],
                    'locks'      => [false, false, false, false],
                    'efficiency' => [5, 5],
                    'results'    => [false, false, false],
                    'rfrs'       => [true, true, false],
                    'message'    => 'Test failing without both locks',
                    'lockPercentage' => [0, 0, 0, 0]
                ]
            ],
        ];

        $rollerPlateDataProvider = function($testType) {
            $tests = [
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [20, 100, 0, 100, 20, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [30, 30],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls above 30',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [20, 100, 0, 90, 10, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [30, 25],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with one control over 30 and second over 25',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [20, 100, 0, 90, 9, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [30, 24],
                        'results'    => [true, false, false],
                        'rfrs'       => [false, false, true],
                        'message'    => 'Test fail with one control over 30 and second under 25',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [18, 90, 0, 90, 10, 0],
                        'locks'      => [true, false, false, true],
                        'efficiency' => [27, 25],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test pass with both controls between 25 and 30 and locks applied',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [18, 90, 0, 90, 10, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [27, 25],
                        'results'    => [false, false, false],
                        'rfrs'       => [false, true, false],
                        'message'    => 'Test fail with both controls between 25 and 30',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [175, 125, 40, 60],
                        'efforts'    => [33, 90, 10, 190, 70, 13],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [33, 68],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls above 30 with sidecar',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [40, 41, 0, 100, 20, 0],
                        'locks'      => [true, true, false, true],
                        'efficiency' => [20, 30],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with one control locked, second above 30, locks',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [185, 135, 0, 80],
                        'efforts'    => [40, 41, 0, 50, 49, 0],
                        'locks'      => [true, true, true, true],
                        'efficiency' => [20, 24],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls locked, locks',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 20, 80],
                        'efforts'    => [24, 50, 0, 33, 5, 0],
                        'locks'      => [true, true, false, true],
                        'efficiency' => [24, 12],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test pass with both controls below 25 and locks on both controls',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 20, 80],
                        'efforts'    => [24, 50, 0, 33, 5, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [24, 12],
                        'results'    => [false, false, false],
                        'rfrs'       => [true, true, false],
                        'message'    => 'Test fail with both controls below 25',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 20, 80],
                        'efforts'    => [null, 101, null, 104, null, null],
                        'locks'      => [null, null, null, null],
                        'efficiency' => [33, 34],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test pass with not linked brakes, null on some values',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 20, 80],
                        'efforts'    => [null, 101, null, null, null, null],
                        'locks'      => [null, null, null, null],
                        'oldBike'    => true,
                        'efficiency' => [33, null],
                        'results'    => [true, null, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test pass with old bike only one control',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 20, 80],
                        'efforts'    => [null, 101, null, null, null, null],
                        'locks'      => [null, null, null, null],
                        'efficiency' => [33, null],
                        'results'    => [true, false, false],
                        'rfrs'       => [false, false, true],
                        'message'    => 'Test fail with new bike only one control',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [225, 175, 0, 0],
                        'efforts'    => [20, 100, 0, 100, 20, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [30, 30],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls above 30',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [225, 175, 0, 0],
                        'efforts'    => [40, 41, 0, 50, 49, 0],
                        'locks'      => [true, true, true, true],
                        'efficiency' => [20, 24],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls locked, floor brake test',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [225, 175, 0, 0],
                        'efforts'    => [40, 41, 0, 50, 49, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [20, 24],
                        'results'    => [false, false, false],
                        'rfrs'       => [true, true, false],
                        'message'    => 'Test failing with both below minimum, floor brake test',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [225, 175, 0, null],
                        'efforts'    => [40, 41, 0, 50, 49, 0],
                        'locks'      => [false, false, false, false],
                        'efficiency' => [20, 24],
                        'results'    => [false, false, false],
                        'rfrs'       => [true, true, false],
                        'message'    => 'Test failing with both below minimum, plate brake test',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [225, 175, 0, null],
                        'efforts'    => [40, 41, 0, 50, 49, 0],
                        'locks'      => [true, true, true, true],
                        'efficiency' => [20, 24],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls locked, plate brake test',
                    ]
                ],
                [
                    [
                        'type'       => $testType,
                        'weights'    => [100, 100, 0, 0],
                        'efforts'    => [58, 0, 0, 0, 50, 0],
                        'locks'      => [true, false, false, true],
                        'efficiency' => [29, 25],
                        'results'    => [true, true, true],
                        'rfrs'       => [false, false, false],
                        'message'    => 'Test passing with both controls locked but efficiency below minimum, roller brake test',
                    ]
                ],
            ];
            return $tests;
        };


        return array_merge($floorDataProvider, $rollerPlateDataProvider(BrakeTestTypeCode::ROLLER), $rollerPlateDataProvider(BrakeTestTypeCode::PLATE));
    }

    /**
     * This is the data provided for the test
     *
     * @dataProvider brakeTestResultItemsDecelerometer
     */
    public function testBrakeTestResultCalculatorForClasses1And2Decelerometer(
        $efficiencies,
        $results,
        $rfrs,
        $firstUsedDate,
        $message
    ) {
        $brakeTestResult = new BrakeTestResultClass12();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::decelerometer())
            ->setControl1BrakeEfficiency($efficiencies[0])
            ->setControl2BrakeEfficiency($efficiencies[1]);
        $firstUsedDate = new \DateTime($firstUsedDate);
        $brakeTestResultCalculator = new BrakeTestResultClass1And2Calculator();
        $brakeTestResultCalculator->calculateBrakeTestResult($brakeTestResult, $firstUsedDate);
        $this->assertBrakeTestResults(
            $brakeTestResult, $brakeTestResultCalculator, $message, $results, $rfrs
        );
    }

    public static function brakeTestResultItemsDecelerometer()
    {
        return [
            [
                'efficiencies' => [30, 25],
                'results'      => [true, true, true],
                'rfrs'         => [false, false, false],
                'firstUsed'    => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'message'      => 'Test passing on good efficiencies',
            ],
            [
                'efficiencies' => [29, 25],
                'results'      => [false, false, false],
                'rfrs'         => [false, true, false],
                'firstUsed'    => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'message'      => 'Test failing no control reaches primary minimum',
            ],
            [
                'efficiencies' => [15, null],
                'results'      => [false, false, false],
                'rfrs'         => [true, true, false],
                'firstUsed'    => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'message'      => 'Test failing no control reaches secondary minimum',
            ],
            [
                'efficiencies' => [30, null],
                'results'      => [true, false, false],
                'rfrs'         => [false, false, true],
                'firstUsed'    => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'message'      => 'Test failing second control not reaching secondary minimum',
            ],
            [
                'efficiencies' => [30, null],
                'results'      => [true, null, true],
                'rfrs'         => [false, false, false],
                'firstUsed'    => self::DATE_BIKE_ONE_CONTROL_ALLOWED,
                'message'      => 'Test pass old bike needs only one control',
            ],
        ];
    }

    /**
     * This is the data provided for the test
     *
     * @dataProvider brakeTestResultItemsGradient
     */
    public function testBrakeTestResultCalculatorForClasses1And2Gradient(
        $controlsBelowMinimum,
        $controlsAboveMaximum,
        $efficiencyPasses,
        $generalPass,
        $firstUsedDate,
        $rfrs,
        $message
    ) {
        $brakeTestResult = new BrakeTestResultClass12();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::gradient())
            ->setGradientControl1AboveUpperMinimum($controlsAboveMaximum[0])
            ->setGradientControl2AboveUpperMinimum($controlsAboveMaximum[1])
            ->setGradientControl1BelowMinimum($controlsBelowMinimum[0])
            ->setGradientControl2BelowMinimum($controlsBelowMinimum[1]);
        $firstUsedDate = new \DateTime($firstUsedDate);
        $brakeTestResultCalculator = new BrakeTestResultClass1And2Calculator();
        $brakeTestResultCalculator->calculateBrakeTestResult($brakeTestResult, $firstUsedDate);
        $this->assertSame($efficiencyPasses[0], $brakeTestResult->getControl1EfficiencyPass(), 'Pass eff 1' . $message);
        $this->assertSame($efficiencyPasses[1], $brakeTestResult->getControl2EfficiencyPass(), 'Pass eff 2' . $message);
        $this->assertSame($generalPass, $brakeTestResult->getGeneralPass(), 'Pass - ' . $message);
        $this->assertRfrs($brakeTestResultCalculator, $brakeTestResult, $rfrs, $message);
    }

    public static function brakeTestResultItemsGradient()
    {
        return [
            [
                'belowMin'    => [false, false],
                'aboveMax'    => [true, true],
                'results'     => [true, true],
                'generalPass' => true,
                'firstUsed'   => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'rfrs'        => [false, false, false],
                'message'     => 'Test passing on good efficiencies',
            ],
            [
                'belowMin'    => [true, false],
                'aboveMax'    => [false, true],
                'results'     => [false, true],
                'generalPass' => false,
                'firstUsed'   => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'rfrs'        => [false, false, true],
                'message'     => 'Test failing on control 1',
            ],
            [
                'belowMin'    => [true, true],
                'aboveMax'    => [false, false],
                'results'     => [false, false],
                'generalPass' => false,
                'firstUsed'   => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'rfrs'        => [true, true, false],
                'message'     => 'Test failing on both below 25',
            ],
            [
                'belowMin'    => [false, true],
                'aboveMax'    => [false, false],
                'results'     => [false, false],
                'generalPass' => false,
                'firstUsed'   => self::DATE_BIKE_TWO_CONTROLS_REQUIRED,
                'rfrs'        => [false, true, false],
                'message'     => 'Test failing on both below 25',
            ],
        ];
    }

    protected function assertRfrs(
        BrakeTestResultClass1And2Calculator $brakeTestResultCalculator,
        $brakeTestResult,
        $rfrs,
        $message
    ) {
        $this->assertSame(
            $rfrs[0],
            $brakeTestResultCalculator->areBothControlsUnderSecondaryMinimum($brakeTestResult),
            'Rfr 1 - ' . $message
        );
        $this->assertSame(
            $rfrs[1],
            $brakeTestResultCalculator->noControlReachesPrimaryMinimum($brakeTestResult),
            'Rfr 2 - ' . $message
        );
        $this->assertSame(
            $rfrs[2],
            $brakeTestResultCalculator->oneControlNotReachingSecondaryMinimum($brakeTestResult),
            'Rfr 3 - ' . $message
        );
    }
}
