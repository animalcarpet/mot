<?php

namespace DvsaMotApiTest\Service\Validator;

use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\BrakeTestResultClass12Test;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaEntitiesTest\Entity\WeightSourceFactory;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;

/**
 * Class BrakeTestResultValidatorTest.
 */
class BrakeTestResultValidatorTest extends AbstractServiceTestCase
{
    const MOCK_VALIDATOR = 'mockBrakeTestConfigurationValidator';

    /**
     * TODO: Change this test to be more flexible (ps).
     *
     * @dataProvider brakeTestValidatorItemsClass3AndAbove
     */
    public function testBrakeTestValidationClass3AndAbove(
        $message,
        $brakeTestResult,
        Vehicle $vehicle,
        $expectedCount = 0,
        $expectedMessage = null
    ) {
        $brakeTestResultValidator = new BrakeTestResultValidator();
        try {
            $brakeTestResultValidator->validateBrakeTestResultClass3AndAbove(
                $brakeTestResult, $vehicle
            );
            $this->assertSame($expectedCount, 0);
        } catch (BadRequestException $ex) {
            $traceMessage = $this->getTraceMessageAfterException(
                $message, $brakeTestResult, $vehicle->getVehicleClass()->getCode(), $ex->getErrors()
            );
            if ($expectedCount === 0) {
                $this->fail('Exception not expected. '.$traceMessage);
            }
            $errors = $ex->getErrors();
            $this->assertCount($expectedCount, $errors, 'Wrong count. '.$traceMessage);
            if ($expectedMessage) {
                foreach ($errors as $error) {
                    $this->assertEquals($expectedMessage, $error['message'], 'Wrong error message. '.$traceMessage);
                }
            }
        }
    }

    protected function getTraceMessageAfterException($testMessage, $data, $vehicleClass, $exceptionErrors)
    {
        return ' Message: ['.$testMessage."] \n
                    Data: [".print_r($data, true).", vehicle class: [$vehicleClass]\n
                    Exception errors: ".print_r($exceptionErrors, true);
    }

    public static function brakeTestValidatorItemsClass3AndAbove()
    {
        return
            [
                //CLASS 4 ROLLER
                [
                    'Test valid brake test result class 4',
                    self::getValidBrakeTestResultRoller(),
                    self::getTestVehicle(),
                ],
                [
                    'Test invalid efforts throw exception',
                    self::getBrakeTestResultClass4InvalidEfforts(),
                    self::getTestVehicle(),
                    8,
                    BrakeTestResultValidator::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL,
                ],
                [
                    'Test invalid locks throw exception',
                    self::getBrakeTestResultClass4InvalidLocks(),
                    self::getTestVehicle(),
                    8,
                    BrakeTestResultValidator::BOOL_VALUE_MESSAGE_LOCK_TRUE_FALSE,
                ],
                [
                    'Test very large brake values for service brake throws exception',
                    self::getBrakeTestResultClass4VeryLargeEfforts(),
                    self::getTestVehicle(),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_EFFORT_VALUE_TOO_LARGE, 'service brake nearside axle 1'),
                ],
                [
                    'Test very large brake values for parking brake throws exception',
                    self::getBrakeTestResultClass4VeryLargeEfforts(false),
                    self::getTestVehicle(),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_EFFORT_VALUE_TOO_LARGE, 'parking brake offside'),
                ],
                [
                    'Test exception thrown on single line past sep 2010',
                    self::getValidBrakeTestResultRoller()
                        ->setServiceBrakeIsSingleLine(true),
                    self::getTestVehicle(
                        Vehicle::VEHICLE_CLASS_4,
                        BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY
                    ),
                    1,
                    'Single line service brake type is not applicable to vehicles past 1 Sep 2010',
                ],
                [
                    'Test class 4 with locks and null efforts does not throw validation message expected only for class 1&2',
                    self::getValidBrakeTestResultRoller()
                        ->setServiceBrake1Data(
                            self::getValidServiceBrakeData()
                                ->setLockNearsideAxle2(true)
                                ->setEffortNearsideAxle2(null)
                        )
                        ->setParkingBrakeLockNearside(true)
                        ->setParkingBrakeEffortNearside(null),
                    self::getTestVehicle(),
                ],
                //DECELEROMETER
                [
                    'Test valid decelerometer class 4',
                    self::getValidBrakeTestResultDecelerometer(),
                    self::getTestVehicle(),
                ],
                [
                    'Test unnecessary service brake data throws exception in decelerometer type test',
                    self::getValidBrakeTestResultDecelerometer()
                        ->setServiceBrake1Data(new BrakeTestResultServiceBrakeData()),
                    self::getTestVehicle(),
                    1,
                    BrakeTestResultValidator::MESSAGE_SERVICE_BRAKE_DATA_NOT_ALLOWED,
                ],
                //SERVICE BRAKE 2 - VARIOUS CLASSES
                [
                    'Test second service brake throws exception for class 5',
                    self::getValidBrakeTestResultRoller()
                        ->setServiceBrake2TestType(BrakeTestTypeFactory::roller())
                        ->setServiceBrake2Data(new BrakeTestResultServiceBrakeData()),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_5),
                    1,
                    BrakeTestResultValidator::MESSAGE_SERVICE_BRAKE_2_DATA_N_A,
                ],
                [
                    'Test second service brake throws exception for class 7',
                    self::getValidBrakeTestResultRoller()
                        ->setServiceBrake2TestType(BrakeTestTypeFactory::roller())
                        ->setServiceBrake2Data(new BrakeTestResultServiceBrakeData()),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_7),
                    1,
                    BrakeTestResultValidator::MESSAGE_SERVICE_BRAKE_2_DATA_N_A,
                ],
                [
                    'Test second service brake all right for class 3',
                    self::getValidBrakeTestResultRoller(Vehicle::VEHICLE_CLASS_3)
                        ->setServiceBrake2TestType(BrakeTestTypeFactory::roller())
                        ->setServiceBrake2Data(self::getValidServiceBrakeData(Vehicle::VEHICLE_CLASS_3)),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_3),
                ],
                //CLASS 3 SERVICE BRAKE 2
                [
                    'Test invalid efforts service brake 2 throws exception',
                    self::getValidBrakeTestResultRoller(Vehicle::VEHICLE_CLASS_3)
                        ->setServiceBrake2TestType(BrakeTestTypeFactory::roller())
                        ->setServiceBrake2Data(self::getInvalidEffortsServiceBrakeData(Vehicle::VEHICLE_CLASS_3)),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_3),
                    3,
                    BrakeTestResultValidator::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL,
                ],
                [
                    'Test invalid locks service brake 2 throws exception',
                    self::getValidBrakeTestResultRoller(Vehicle::VEHICLE_CLASS_3)
                        ->setServiceBrake2TestType(BrakeTestTypeFactory::roller())
                        ->setServiceBrake2Data(self::getInvalidLocksServiceBrakeData(Vehicle::VEHICLE_CLASS_3)),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_3),
                    3,
                    BrakeTestResultValidator::BOOL_VALUE_MESSAGE_LOCK_TRUE_FALSE,
                ],
                //CLASS 3
                [
                    'Test empty single effort parking brake throws exception',
                    self::getValidBrakeTestResultRoller(Vehicle::VEHICLE_CLASS_3)
                        ->setParkingBrakeEffortSingle(''),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_3),
                    1,
                ],
                [
                    'Test valid efforts class 4 throw exception on class 3 vehicle',
                    self::getValidBrakeTestResultRoller(Vehicle::VEHICLE_CLASS_4),
                    self::getTestVehicle(Vehicle::VEHICLE_CLASS_3),
                    4,
                ],
            ];
    }

    /**
     * @dataProvider brakeTestValidatorItemsClass1And2
     */
    public function testBrakeTestValidationClass1And2(
        $message,
        $brakeTestResult,
        $expectedCount = 0,
        $expectedMessage = null,
        $bikeFirstUsed = null
    ) {
        if (!$bikeFirstUsed) {
            $bikeFirstUsed = self::getNewBikeDate();
        }
        $brakeTestResultValidator = new BrakeTestResultValidator();
        try {
            $brakeTestResultValidator->validateBrakeTestResultClass1And2(
                $brakeTestResult, $bikeFirstUsed
            );
            $this->assertEquals($expectedCount, 0);
        } catch (ServiceException $ex) {
            $errors = $ex->getErrors();
            $this->assertCount($expectedCount, $errors, $message);
            if ($expectedMessage) {
                foreach ($errors as $error) {
                    $this->assertEquals($expectedMessage, $error['message']);
                }
            }
        }
    }

    public static function brakeTestValidatorItemsClass1And2()
    {
        return
            [
                [
                    'Test valid brake test result class 1,2',
                    self::getValidBrakeTestResultClass1And2(),
                ],
                [
                    'Test invalid locks throw exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1LockFront(-1)
                        ->setControl1LockRear(2)
                        ->setControl2LockFront(3)
                        ->setControl2LockRear('aa'),
                    4,
                    BrakeTestResultValidator::BOOL_VALUE_MESSAGE_LOCK_TRUE_FALSE,
                ],
                [
                    'Test invalid efforts throw exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(-1)
                        ->setControl1EffortRear(true)
                        ->setControl2EffortFront('a')
                        ->setControl2EffortRear('aa')
                        ->setControl1EffortSidecar(-12)
                        ->setControl2EffortSidecar(false),
                    6,
                    BrakeTestResultValidator::MESSAGE_EFFORT_POSITIVE_NUMBER_OR_NULL,
                ],
                [
                    'Test empty control 1 throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(null)
                        ->setControl1EffortRear(null),
                    1,
                    BrakeTestResultValidator::MESSAGE_CONTROL_1_EMPTY,
                ],
                [
                    'Test very large control 1 front throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(99999)
                        ->setControl1EffortRear(100),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_EFFORT_VALUE_TOO_LARGE, 'control 1 effort front'),
                ],
                [
                    'Test very large control 1 rear throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(100)
                        ->setControl1EffortRear(99999),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_EFFORT_VALUE_TOO_LARGE, 'control 1 effort rear'),
                ],
                [
                    'Test empty front both controls throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(null)
                        ->setControl2EffortFront(null),
                    1,
                    BrakeTestResultValidator::MESSAGE_ONE_CONTROL_FRONT,
                ],
                [
                    'Test empty rear both controls throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortRear(null)
                        ->setControl2EffortRear(null),
                    1,
                    BrakeTestResultValidator::MESSAGE_ONE_CONTROL_REAR,
                ],
                [
                    'Test empty control2 throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl2EffortFront(null)
                        ->setControl2EffortRear(null),
                    1,
                    BrakeTestResultValidator::MESSAGE_CONTROL_2_EMPTY,
                ],
                [
                    'Test empty control 2 on old bike not throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortRear(null)
                        ->setControl2EffortRear(null),
                    0,
                    null,
                    self::getOldBikeDate(),
                ],
                [
                    'Test valid floor type brake test',
                    self::getValidBrakeTestResultClass1And2TypeFloor(),
                ],
                [
                    'Test null front efforts throw exception',
                    self::getValidBrakeTestResultClass1And2TypeFloor()
                        ->setControl1EffortFront(null)
                        ->setControl2EffortFront(null),
                    2,
                    BrakeTestResultValidator::MESSAGE_FLOOR_FRONT_EFFORT_NUMBER,
                ],
                [
                    'Test values rear efforts throw exception in floor type test',
                    self::getValidBrakeTestResultClass1And2TypeFloor()
                        ->setControl1EffortRear(12)
                        ->setControl2EffortRear(13)
                        ->setControl1EffortSidecar(14)
                        ->setControl2EffortSidecar(15),
                    4,
                    BrakeTestResultValidator::MESSAGE_FLOOR_ONLY_FRONT_APPLICABLE,
                ],
                [
                    'Test valid decelerometer brake test',
                    self::getValidBrakeTestResultClass1And2TypeDecelerometer(),
                ],
                [
                    'Test valid roller type throws exception if set to decelerometer',
                    self::getValidBrakeTestResultClass1And2()
                        ->setBrakeTestType(BrakeTestTypeFactory::decelerometer())
                        ->setControl1BrakeEfficiency(null)
                        ->setControl2BrakeEfficiency(null),
                    12,
                ],
                [
                    'Test valid roller type throws exception if set to decelerometer',
                    self::getValidBrakeTestResultClass1And2TypeDecelerometer()
                        ->setControl1BrakeEfficiency(40)
                        ->setControl2BrakeEfficiency(null),
                    0,
                    null,
                    self::getOldBikeDate(),
                ],
                [
                    'Test valid gradient type test',
                    self::getValidBrakeTestResultClass1And2TypeGradient(),
                ],
                [
                    'Test gradient type test is valid for old bike',
                    self::getValidBrakeTestResultClass1And2TypeGradient()
                        ->setControl1EfficiencyPass(false)
                        ->setControl2EfficiencyPass(null),
                    0,
                    null,
                    self::getOldBikeDate(),
                ],
                [
                    'Test valid gradient type test',
                    self::getValidBrakeTestResultClass1And2TypeGradient()
                        ->setGradientControl1BelowMinimum(0)
                        ->setGradientControl2BelowMinimum(1),
                    2,
                    BrakeTestResultValidator::MESSAGE_GRADIENT_CONTROLS_BELOW_BOOL,
                ],
                [
                    'Test valid gradient type test',
                    self::getValidBrakeTestResultClass1And2TypeGradient()
                        ->setGradientControl1AboveUpperMinimum(0)
                        ->setGradientControl2AboveUpperMinimum(1),
                    2,
                    BrakeTestResultValidator::MESSAGE_GRADIENT_CONTROLS_ABOVE_BOOL,
                ],
                [
                    'Wrong gradient settings',
                    self::getValidBrakeTestResultClass1And2TypeGradient()
                        ->setGradientControl1BelowMinimum(true)
                        ->setGradientControl2BelowMinimum(true)
                        ->setGradientControl1AboveUpperMinimum(true)
                        ->setGradientControl2AboveUpperMinimum(true),
                    2,
                    BrakeTestResultValidator::MESSAGE_GRADIENT_CONTROLS_MINIMUMS_INVALID,
                ],
                [
                    'Test empty control 1 front with lock box checked throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(null)
                        ->setControl1LockFront(true),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_BRAKE_EFFORT_POSITIVE_IF_LOCK_BOX_CHECKED, 'Control one', 'front'),
                ],
                [
                    'Test filled control 1 rear with lock box checked does not throw exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl1EffortFront(100)
                        ->setControl1LockFront(true),
                    0,
                    null,
                ],
                [
                    'Test empty control 2 rear with lock box checked throws exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl2EffortRear(null)
                        ->setControl2LockRear(true),
                    1,
                    sprintf(BrakeTestResultValidator::MESSAGE_BRAKE_EFFORT_POSITIVE_IF_LOCK_BOX_CHECKED, 'Control two', 'rear'),
                ],
                [
                    'Test empty control 2 rear with lock box not checked does not throw exception',
                    self::getValidBrakeTestResultClass1And2()
                        ->setControl2EffortRear(null)
                        ->setControl2LockRear(false),
                    0,
                    null,
                ],
            ];
    }

    /**
     * @return \DvsaEntities\Entity\BrakeTestResultClass12
     */
    protected static function getValidBrakeTestResultClass1And2()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();

        return $brakeTestResult;
    }

    protected static function getValidBrakeTestResultClass1And2TypeFloor()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::floor())
            ->setControl1EffortFront(30)
            ->setControl2EffortFront(25)
            ->setControl1EffortRear(null)
            ->setControl2EffortRear(null)
            ->setControl1EffortSidecar(null)
            ->setControl2EffortSidecar(null)
            ->setControl1LockFront(null)
            ->setControl1LockRear(null)
            ->setControl2LockFront(null)
            ->setControl2LockRear(null);

        return $brakeTestResult;
    }

    protected static function getValidBrakeTestResultClass1And2TypeDecelerometer()
    {
        $brakeTestResult = BrakeTestResultClass12Test::getTestBrakeTestResult();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::decelerometer())
            ->setControl1BrakeEfficiency(30)
            ->setControl2BrakeEfficiency(25)
            ->setControl1EffortFront(null)
            ->setControl1EffortRear(null)
            ->setControl2EffortFront(null)
            ->setControl2EffortRear(null)
            ->setControl1EffortSidecar(null)
            ->setControl2EffortSidecar(null)
            ->setControl1LockFront(null)
            ->setControl1LockRear(null)
            ->setControl2LockFront(null)
            ->setControl2LockRear(null);

        return $brakeTestResult;
    }

    protected static function getValidBrakeTestResultClass1And2TypeGradient()
    {
        $brakeTestResult = self::getValidBrakeTestResultClass1And2TypeDecelerometer();
        $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::gradient())
            ->setControl1BrakeEfficiency(null)
            ->setControl2BrakeEfficiency(null)
            ->setControl1EfficiencyPass(true)
            ->setControl2EfficiencyPass(true)
            ->setGradientControl1BelowMinimum(false)
            ->setGradientControl2BelowMinimum(false)
            ->setGradientControl1AboveUpperMinimum(true)
            ->setGradientControl2AboveUpperMinimum(true);

        return $brakeTestResult;
    }

    protected static function getOldBikeDate()
    {
        return new \DateTime('1926-12-31');
    }

    protected static function getNewBikeDate()
    {
        return new \DateTime('1927-01-01');
    }

    protected static function getValidBrakeTestResultRoller($vehicleClass = Vehicle::VEHICLE_CLASS_4)
    {
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::roller())
            ->setParkingBrakeTestType(BrakeTestTypeFactory::roller())
            ->setVehicleWeight(2000)
            ->setWeightType(WeightSourceFactory::presented())
            ->setParkingBrakeEffortNearside(140)
            ->setParkingBrakeEffortOffside(141)
            ->setParkingBrakeLockNearside(false)
            ->setParkingBrakeLockOffside(false);
        $serviceBrake1 = self::getValidServiceBrakeData($vehicleClass);
        $brakeTestResult->setServiceBrake1Data($serviceBrake1);

        return $brakeTestResult;
    }

    protected static function getValidServiceBrakeData($vehicleClass = Vehicle::VEHICLE_CLASS_4)
    {
        $serviceBrake = new BrakeTestResultServiceBrakeData();
        $serviceBrake
            ->setEffortNearsideAxle1(230)
            ->setEffortOffsideAxle1(231)
            ->setLockNearsideAxle1(false)
            ->setLockOffsideAxle1(false);
        if ($vehicleClass === Vehicle::VEHICLE_CLASS_3) {
            $serviceBrake
                ->setEffortSingle(240)
                ->setLockSingle(false)
                ->setLockNearsideAxle2(null)
                ->setLockOffsideAxle2(null);
        } else {
            $serviceBrake
                ->setEffortNearsideAxle2(240)
                ->setEffortOffsideAxle2(241)
                ->setLockNearsideAxle2(false)
                ->setLockOffsideAxle2(false);
        }

        return $serviceBrake;
    }

    protected static function getValidBrakeTestResultDecelerometer()
    {
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::decelerometer())
            ->setParkingBrakeTestType(BrakeTestTypeFactory::decelerometer())
            ->setParkingBrakeEfficiency(30)
            ->setServiceBrake1Efficiency(34);

        return $brakeTestResult;
    }

    protected static function getBrakeTestResultClass4InvalidEfforts()
    {
        $brakeTestResult = self::getValidBrakeTestResultRoller();
        $brakeTestResult
            ->setParkingBrakeEffortNearside('a')
            ->setParkingBrakeEffortOffside(true)
            ->setParkingBrakeEffortSecondaryOffside(false)
            ->setParkingBrakeEffortSecondaryNearside('b');
        $serviceBrakeData = self::getInvalidEffortsServiceBrakeData();

        return $brakeTestResult
            ->setServiceBrake1Data($serviceBrakeData);
    }

    protected static function getBrakeTestResultClass4VeryLargeEfforts($serviceBrake = true)
    {
        $brakeTestResult = self::getValidBrakeTestResultRoller();
        if ($serviceBrake) {
            $brakeTestResult
                ->setParkingBrakeEffortNearside(100)
                ->setParkingBrakeEffortOffside(100)
                ->setParkingBrakeEffortSecondaryOffside(null)
                ->setParkingBrakeEffortSecondaryNearside(null);

            $serviceBrakeData = self::getVeryLargeEffortsServiceBrakeData();
        } else {
            $brakeTestResult
                ->setParkingBrakeEffortNearside(100)
                ->setParkingBrakeEffortOffside(999999)
                ->setParkingBrakeEffortSecondaryOffside(null)
                ->setParkingBrakeEffortSecondaryNearside(null);
            $serviceBrakeData = self::getValidServiceBrakeData();
        }

        return $brakeTestResult
            ->setServiceBrake1Data($serviceBrakeData);
    }

    protected static function getInvalidEffortsServiceBrakeData($vehicleClass = Vehicle::VEHICLE_CLASS_4)
    {
        $serviceBrakeData = self::getValidServiceBrakeData($vehicleClass);
        $serviceBrakeData
            ->setEffortNearsideAxle1(-1)
            ->setEffortOffsideAxle1(false);
        if ($vehicleClass === Vehicle::VEHICLE_CLASS_3) {
            $serviceBrakeData
                ->setEffortSingle('buh')
                ->setEffortNearsideAxle2(null)
                ->setEffortOffsideAxle2(null);
        } else {
            $serviceBrakeData
                ->setEffortNearsideAxle2('a')
                ->setEffortOffsideAxle2(-5);
        }

        return $serviceBrakeData;
    }

    protected static function getVeryLargeEffortsServiceBrakeData($vehicleClass = Vehicle::VEHICLE_CLASS_4)
    {
        $serviceBrakeData = self::getValidServiceBrakeData($vehicleClass);
        $serviceBrakeData
            ->setEffortNearsideAxle1(99999)
            ->setEffortOffsideAxle1(100)
            ->setEffortNearsideAxle2(100)
            ->setEffortOffsideAxle2(100);

        return $serviceBrakeData;
    }

    protected static function getBrakeTestResultClass4InvalidLocks()
    {
        $brakeTestResult = self::getValidBrakeTestResultRoller();
        $brakeTestResult
            ->setParkingBrakeLockNearside('m')
            ->setParkingBrakeLockOffside(0)
            ->setParkingBrakeLockSecondaryNearside(1)
            ->setParkingBrakeLockSecondaryOffside('n');
        $serviceBrakeData = self::getInvalidLocksServiceBrakeData();

        return $brakeTestResult
            ->setServiceBrake1Data($serviceBrakeData);
    }

    protected static function getInvalidLocksServiceBrakeData($vehicleClass = Vehicle::VEHICLE_CLASS_4)
    {
        $serviceBrakeData = self::getValidServiceBrakeData($vehicleClass);
        $serviceBrakeData
            ->setLockNearsideAxle1(-1)
            ->setLockOffsideAxle1(0);
        if ($vehicleClass === Vehicle::VEHICLE_CLASS_3) {
            $serviceBrakeData
                ->setEffortNearsideAxle2(null)
                ->setEffortOffsideAxle2(null)
                ->setLockSingle(0);
        } else {
            $serviceBrakeData
                ->setLockNearsideAxle2('a')
                ->setLockOffsideAxle2(1);
        }

        return $serviceBrakeData;
    }

    private static function getTestVehicle($vehicleClass = Vehicle::VEHICLE_CLASS_4, $firstUsed = '2008-01-01')
    {
        $vehicle = new Vehicle();

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass(new VehicleClass($vehicleClass));

        return $vehicle
            ->setModelDetail($modelDetail)
            ->setFirstUsedDate(new \DateTime($firstUsed));
    }
}
