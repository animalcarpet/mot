<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaEntitiesTest\Entity\WeightSourceFactory;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;

/**
 * Unit tests for BrakeTestResultClass3AndAboveCalculatorTest.
 */
class BrakeTestResultClass3AndAboveCalculatorTest extends \PHPUnit_Framework_TestCase
{
    const DATE_DEFAULT_FIRST_USED = '2008-01-01';

    /**
     * This is the data provided for the test.
     *
     * @dataProvider allData
     */
    public function testBrakeTestResults($testData)
    {
        $testData = array_replace_recursive($this->coreDefaults(), $testData);

        $input = $testData['input'];
        $output = $testData['output'];
        $testName = $testData['desc'];
        if ($testName === 'RxR, Test fail on both controls below 25 class 3, imbalance not counted') {
            $x = 1;
        }

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass(new VehicleClass($input['vehicleClass']));

        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setFirstUsedDate(new \DateTime($input['vehicleFirstUsed']));
        $serviceBrake1Test = $input['serviceBrake1Test'];
        $serviceBrake2Test = $input['serviceBrake2Test'];
        $isSingleInFront = $input['isSingleInFront'];
        $isCommercialVehicle = $input['isCommercialVehicle'];
        $parkingBrakeTest = $input['parkingBrakeTest'];
        $serviceBrake1TestType = $serviceBrake1Test['type'];
        $serviceBrake2TestType = $serviceBrake2Test['type'];
        $parkingBrakeTestType = $parkingBrakeTest['type'];

        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $serviceBrake1Data = new BrakeTestResultServiceBrakeData();
        $serviceBrake2Data = new BrakeTestResultServiceBrakeData();

        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::type($serviceBrake1TestType))
            ->setServiceBrake1Data($serviceBrake1Data)
            ->setServiceBrake2TestType(
                $serviceBrake2TestType ? BrakeTestTypeFactory::type($serviceBrake2TestType) : null
            )
            ->setServiceBrake2Data($serviceBrake2Test ? $serviceBrake2Data : null)
            ->setIsCommercialVehicle($isCommercialVehicle)
            ->setIsSingleInFront($isSingleInFront);

        if (in_array($serviceBrake1TestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            $this->fillInServiceBrakeData($serviceBrake1TestType, $serviceBrake1Test, $serviceBrake1Data);
        }

        if (in_array($serviceBrake2TestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])
            && $serviceBrake2Test) {
            $this->fillInServiceBrakeData($serviceBrake2TestType, $serviceBrake2Test, $serviceBrake2Data);
        }

        if ($serviceBrake1TestType === BrakeTestTypeCode::DECELEROMETER) {
            $brakeTestResult->setServiceBrake1Efficiency($serviceBrake1Test['serviceBrake1Efficiency']);
            if (isset($serviceBrake2Test['serviceBrake2Efficiency'])) {
                $brakeTestResult->setServiceBrake2Efficiency($serviceBrake2Test['serviceBrake2Efficiency']);
            }
        }

        $brakeTestResult->setParkingBrakeTestType(BrakeTestTypeFactory::type($parkingBrakeTestType));
        if (in_array($parkingBrakeTestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            $parkingBrakeTest = array_replace_recursive($this->axleDefaults(), $parkingBrakeTest);
            $brakeTestResult
                ->setParkingBrakeEffortNearside($parkingBrakeTest['efforts']['near'])
                ->setParkingBrakeEffortOffside($parkingBrakeTest['efforts']['off'])
                ->setParkingBrakeEffortSingle($parkingBrakeTest['efforts']['single'])
                ->setParkingBrakeEffortSecondaryNearside($parkingBrakeTest['efforts']['nearSecondary'])
                ->setParkingBrakeEffortSecondaryOffside($parkingBrakeTest['efforts']['offSecondary'])
                ->setParkingBrakeLockNearside(in_array('near', $parkingBrakeTest['locks']))
                ->setParkingBrakeLockOffside(in_array('off', $parkingBrakeTest['locks']))
                ->setParkingBrakeLockSingle($parkingBrakeTest['locks']['single']);
            if ($brakeTestResult->getParkingBrakeEffortSecondaryNearside() !== null
                || $brakeTestResult->getParkingBrakeEffortSecondaryOffside() !== null
            ) {
                $brakeTestResult
                    ->setParkingBrakeLockSecondaryNearside(in_array('nearSecondary', $parkingBrakeTest['locks']))
                    ->setParkingBrakeLockSecondaryOffside(in_array('offSecondary', $parkingBrakeTest['locks']));
            }
        }

        if ($parkingBrakeTestType !== BrakeTestTypeCode::GRADIENT) {
            $brakeTestResult->setServiceBrakeIsSingleLine($input['isSingleLine']);
        }

        if ($parkingBrakeTestType === BrakeTestTypeCode::DECELEROMETER) {
            $brakeTestResult->setParkingBrakeEfficiency($parkingBrakeTest['parkingBrakeEfficiency']);
        }

        if ($parkingBrakeTestType === BrakeTestTypeCode::GRADIENT) {
            $brakeTestResult->setParkingBrakeEfficiencyPass($parkingBrakeTest['parkingBrakeEfficiencyPass']);
        }

        if (in_array($serviceBrake1TestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])
            || in_array($parkingBrakeTestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])
        ) {
            $input = array_replace_recursive(['weight' => ['unladen' => null]], $input);
            $brakeTestResult->setVehicleWeight($input['weight']['value'])
                ->setWeightType(WeightSourceFactory::type($input['weight']['type']))
                ->setWeightIsUnladen($input['weight']['unladen']);
        }

        $brakeTestResultCalculator = new BrakeTestResultClass3AndAboveCalculator();
        $brakeTestResultCalculator->calculateBrakeTestResult($brakeTestResult, $vehicle);

        $serviceBrakeDataApplicableTypes = [
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
            BrakeTestTypeCode::DECELEROMETER,
        ];
        if (in_array($serviceBrake1TestType, $serviceBrakeDataApplicableTypes)
            || in_array($serviceBrake2TestType, $serviceBrakeDataApplicableTypes)
        ) {
            $output = array_merge_recursive(['efficiency' => []], $output);
            if (isset($output['efficiency']['serviceBrake1'])) {
                $this->assertEquals(
                    $output['efficiency']['serviceBrake1'],
                    $brakeTestResult->getServiceBrake1Efficiency(), $testName
                );
            }
            if (isset($output['efficiency']['serviceBrake2'])) {
                $this->assertEquals(
                    $output['efficiency']['serviceBrake2'],
                    $brakeTestResult->getServiceBrake2Efficiency(), $testName
                );
            }
        }

        if (in_array(
            $parkingBrakeTestType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::DECELEROMETER,
            ]
        )
        ) {
            $output = array_merge_recursive(['efficiency' => []], $output);
            if (isset($output['efficiency']['parkingBrake'])) {
                $this->assertEquals(
                    $output['efficiency']['parkingBrake'],
                    $brakeTestResult->getParkingBrakeEfficiency(), $testName
                );
            }
        }

        $this->checkServiceBrakeAxlesImbalance($output, $serviceBrake1Data, $serviceBrake1TestType, 1, $testName);
        $this->checkServiceBrakeAxlesImbalance($output, $serviceBrake2Data, $serviceBrake2TestType, 2, $testName);

        if (in_array($parkingBrakeTestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            $output = array_replace_recursive(
                ['imbalanceParkingBrake' => null, 'imbalanceSecondaryParkingBrake'], $output
            );
            if (isset($output['imbalanceParkingBrake'])) {
                $this->assertEquals(
                    $output['imbalanceParkingBrake'],
                    $brakeTestResult->getParkingBrakeImbalance(), "Parking brake imbalance value for $testName"
                );
            }
            if (isset($output['imbalanceSecondaryParkingBrake'])) {
                $this->assertEquals(
                    $output['imbalanceSecondaryParkingBrake'],
                    $brakeTestResult->getParkingBrakeSecondaryImbalance(),
                    "Parking brake secondary imbalance value for $testName"
                );
            }
        }

        $output = array_replace_recursive(['passes' => []], $output);
        if (isset($output['passes']['serviceBrake1Efficiency'])) {
            $this->assertEquals(
                $output['passes']['serviceBrake1Efficiency'],
                $brakeTestResult->getServiceBrake1EfficiencyPass(), $testName
            );
        }
        if (isset($output['passes']['serviceBrake2Efficiency'])) {
            $this->assertEquals(
                $output['passes']['serviceBrake2Efficiency'],
                $brakeTestResult->getServiceBrake2EfficiencyPass(), $testName
            );
        }
        if (isset($output['passes']['parkingBrakeEfficiency'])) {
            $this->assertEquals(
                $output['passes']['parkingBrakeEfficiency'],
                $brakeTestResult->getParkingBrakeEfficiencyPass(), $testName
            );
        }
        if (in_array($serviceBrake1TestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            if (isset($output['passes']['imbalanceServiceBrake1'])) {
                $this->assertEquals(
                    $output['passes']['imbalanceServiceBrake1'], $serviceBrake1Data->getImbalancePass(), $testName
                );
            }
        }

        if (in_array($parkingBrakeTestType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            if (isset($output['passes']['parkingBrakeImbalance'])) {
                $this->assertEquals(
                    $output['passes']['parkingBrakeImbalance'],
                    $brakeTestResult->getParkingBrakeImbalancePass(), "Parking brake imbalance pass for $testName"
                );
            }
        }
        if (isset($output['passes']['general'])) {
            $this->assertEquals(
                $output['passes']['general'],
                $brakeTestResult->getGeneralPass(),
                "General pass for $testName"
            );
        }
    }

    private function checkServiceBrakeAxlesImbalance(
        $output,
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $serviceBrakeType,
        $serviceBrakeNumber,
        $testName
    ) {
        if (in_array($serviceBrakeType, [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE])) {
            $outputKey = 'imbalanceServiceBrake'.$serviceBrakeNumber;

            if (isset($output[$outputKey])) {
                $output = array_replace_recursive(
                    [$outputKey => ['axle1' => 0, 'axle2' => 0, 'axle3' => 0]], $output
                );
                $expectedServiceBrakeData = $output[$outputKey];

                $this->assertEquals(
                    $expectedServiceBrakeData['axle1'], $serviceBrakeData->getImbalanceAxle1(), $testName
                );
                $this->assertEquals(
                    $expectedServiceBrakeData['axle2'], $serviceBrakeData->getImbalanceAxle2(), $testName
                );
                $this->assertEquals(
                    $expectedServiceBrakeData ['axle3'], $serviceBrakeData->getImbalanceAxle3(), $testName
                );

                if (isset($expectedServiceBrakeData ['axle1Pass'])) {
                    $this->assertEquals(
                        $expectedServiceBrakeData ['axle1Pass'], $serviceBrakeData->getImbalancePassForAxle(1),
                        $testName
                    );
                }
                if (isset($expectedServiceBrakeData ['axle2Pass'])) {
                    $this->assertEquals(
                        $expectedServiceBrakeData ['axle2Pass'], $serviceBrakeData->getImbalancePassForAxle(2),
                        $testName
                    );
                }
                if (isset($expectedServiceBrakeData ['axle3Pass'])) {
                    $this->assertEquals(
                        $expectedServiceBrakeData ['axle3Pass'], $serviceBrakeData->getImbalancePassForAxle(3),
                        $testName
                    );
                }
            }
        }
    }

    private function fillInServiceBrakeData(
        $serviceBrakeTestType,
        $serviceBrakeTest,
        BrakeTestResultServiceBrakeData $serviceBrakeData
    ) {
        $serviceBrakeTest = array_replace_recursive(['axle1' => $this->axleDefaults()], $serviceBrakeTest);
        $serviceBrakeTest = array_replace_recursive(['axle2' => $this->axleDefaults()], $serviceBrakeTest);
        $serviceBrakeTest = array_replace_recursive(['axle3' => $this->axleDefaults()], $serviceBrakeTest);
        $serviceBrakeTest = array_replace_recursive(
            ['single' => ['effort' => null, 'lock' => null, 'inFront' => null]], $serviceBrakeTest
        );
        $serviceBrakeTest = array_replace_recursive(['single' => null], $serviceBrakeTest);
        $serviceBrakeData
            ->setEffortNearsideAxle1($serviceBrakeTest['axle1']['efforts']['near'])
            ->setEffortOffsideAxle1($serviceBrakeTest['axle1']['efforts']['off'])
            ->setEffortNearsideAxle2($serviceBrakeTest['axle2']['efforts']['near'])
            ->setEffortOffsideAxle2($serviceBrakeTest['axle2']['efforts']['off'])
            ->setEffortNearsideAxle3($serviceBrakeTest['axle3']['efforts']['near'])
            ->setEffortOffsideAxle3($serviceBrakeTest['axle3']['efforts']['off'])
            ->setEffortSingle($serviceBrakeTest['single']['effort']);

        if (in_array($serviceBrakeTestType, [BrakeTestTypeCode::ROLLER])) {
            $serviceBrakeData->setLockNearsideAxle1(in_array('near', $serviceBrakeTest['axle1']['locks']))
                ->setLockOffsideAxle1(in_array('off', $serviceBrakeTest['axle1']['locks']))
                ->setLockNearsideAxle2(in_array('near', $serviceBrakeTest['axle2']['locks']))
                ->setLockOffsideAxle2(in_array('off', $serviceBrakeTest['axle2']['locks']))
                ->setLockNearsideAxle3(in_array('near', $serviceBrakeTest['axle3']['locks']))
                ->setLockOffsideAxle3(in_array('off', $serviceBrakeTest['axle3']['locks']))
                ->setLockSingle($serviceBrakeTest['single']['lock']);
        }
    }

    private function axleDefaults()
    {
        return ['efforts' => ['near' => null, 'off' => null, 'nearSecondary' => null, 'offSecondary' => null,
                              'single' => null, ], 'locks' => ['single' => null]];
    }

    private function coreDefaults()
    {
        return ['input' => [
            'vehicleClass' => '',
            'vehicleFirstUsed' => self::DATE_DEFAULT_FIRST_USED,
            'isSingleLine' => null,
            'isSingleInFront' => null,
            'isCommercialVehicle' => null,
            'weight' => [],
            'serviceBrake1Test' => [],
            'serviceBrake2Test' => null,
            'parkingBrakeTest' => [],
        ],
                'output' => [
                    'passes' => [],
                ], ];
    }

    public static function serviceBrakeDeceleratorAndParkingBrakeGradientTestData()
    {
        return [
            [[
                 'desc' => 'D+G, class4, service brake decelerator above threshold, parking brake gradient fails, all fail', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 50, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::GRADIENT,
                                                  'parkingBrakeEfficiencyPass' => false, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => false,
                                     'general' => false, ],
                    ],
             ]],
            [[
                 'desc' => 'D+G, class4, service brake decelerator above threshold, parking brake gradient passes, all pass', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 50, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::GRADIENT,
                                                  'parkingBrakeEfficiencyPass' => true, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                     'general' => true, ],
                    ],
             ]],
        ];
    }

    public static function allDecelerometerTestData()
    {
        return [
            [[
                 'desc' => 'D+D, class4, service and parking brake threshold efficiencies for dual line, all pass', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => false, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 50, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'parkingBrakeEfficiency' => 16, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                     'general' => true, ],
                    ],
             ]],
            [[
                 'desc' => 'D+D, class4, service and parking brake threshold efficiencies for single line, all pass', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 50, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'parkingBrakeEfficiency' => 25, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => true, 'parkingBrakeEfficiency' => true,
                                     'general' => true, ],
                    ],
             ]],
            [[
                 'desc' => 'D+D, class4, service and parking brake below threshold,', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 49, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'parkingBrakeEfficiency' => 24, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => false,
                                     'general' => false, ],
                    ],
             ]],
            [[
                 'desc' => 'D+D, class4,  service brake below and parking brake above threshold, general fails', 'input' => [
                        'vehicleClass' => VehicleClassCode::CLASS_4, 'isSingleLine' => true, 'serviceBrake1Test' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'serviceBrake1Efficiency' => 49, ], 'parkingBrakeTest' => ['type' => BrakeTestTypeCode::DECELEROMETER,
                                                  'parkingBrakeEfficiency' => 25, ],
                    ], 'output' => [
                        'passes' => ['serviceBrake1Efficiency' => false, 'parkingBrakeEfficiency' => true,
                                     'general' => false, ],
                    ],
             ]],
        ];
    }

    public static function allData()
    {
        return array_merge(
            BrakeTestResultClass4CalculatorImbalanceTestData::rollersAxleImbalancePassTest(),
            BrakeTestResultClass4CalculatorImbalanceTestData::rollersImbalanceTests(),
            self::allDecelerometerTestData(),
            self::serviceBrakeDeceleratorAndParkingBrakeGradientTestData(),
            BrakeTestResultClassRollerTestData::allRollerTestData(),
            BrakeTestResultClass3TestData::class3TwoServiceBrakeTestData(),
            BrakeTestResultClass3TestData::class3SingleServiceBrakeTestData()
        );
    }
}
