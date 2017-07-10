<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\RollerBrakeTestClass3To7;
use Dvsa\Mot\Behat\Support\Data\BrakeTestResultData;
use Dvsa\Mot\Behat\Support\Data\Builder\Class3To7BrakeTestResultsBuilder;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\OdometerReadingData;
use Dvsa\Mot\Behat\Support\Data\Params\BrakeTestResultParams;
use Dvsa\Mot\Behat\Support\Data\Params\MeterReadingParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;
use Zend\Http\Response as HttpResponse;

class BrakeTestResultContext implements Context
{
    private $brakeTestResult;

    private $userData;

    private $motTestData;

    private $brakeTestResultData;

    private $odometerReadingData;

    public function __construct(
        BrakeTestResult $brakeTestResult,
        UserData $userData,
        MotTestData $motTestData,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData
    )
    {
        $this->brakeTestResult = $brakeTestResult;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->brakeTestResultData = $brakeTestResultData;
        $this->odometerReadingData = $odometerReadingData;
    }

    /**
     * @Given the Tester adds a Class 3-7 Roller Brake Test Result
     */
    public function theTesterAddsAClass3To7RollerBrakeTestResult()
    {
        $this->brakeTestResultData->addBrakeTestRollerClass3To7($this->motTestData->getLast());
    }

    /**
     * @Given the Tester adds a Class 3-7 Decelerometer Brake Test
     */
    public function theTesterAddsAClass3To7DecelerometerBrakeTest()
    {
        $this->brakeTestResultData->addBrakeTestDecelerometerClass3To7($this->motTestData->getLast());
    }

    /**
     * @Given /^the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom (.*)$/
     */
    public function theTesterAddsAClass3To7DecelerometerBrakeTestWithCustomData($scenario)
    {
        $this->brakeTestResultData->addBrakeTestDecelerometerClass3To7WithCustomData(
            $this->motTestData->getLast(),
            $this->getDecelerometerBrakeTestDataForClass3To7($scenario)
        );
    }

    /**
     * @Given the Tester adds a Class 1-2 Decelerometer Brake Test
     */
    public function theTesterAddsAClass1DecelerometerBrakeTest()
    {
        $this->brakeTestResultData->addDefaultBrakeTestDecelerometerClass1To2($this->motTestData->getLast());
    }

    /**
     * @Given /^the Tester adds a Class 1-2 Decelerometer Brake Test with custom brake data (.*)$/
     */
    public function theTesterAddsAClass1DecelerometerBrakeTestWithCustomBrakeData($scenario)
    {
        $this->brakeTestResultData->addBrakeTestDecelerometerClass1To2WithCustomData(
            $this->motTestData->getLast(),
            $this->getDecelerometerBrakeTestDataForClass1To2($scenario)
        );
    }

    /**
     * @Given /^I add roller brake test data for (.*)$/
     */
    public function iAddRollerBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultData->addBrakeTestForRollerClass1To2WithCustomData(
            $this->motTestData->getLast(),
            $this->getRollerBrakeTestObjectForClass1And2($scenario)
        );
    }

    /**
     * @Given /^I add plate brake test data for (.*)$/
     */
    public function iAddPlateBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultData->addBrakeTestForPlateClass1To2WithCustomData(
            $this->motTestData->getLast(),
            $this->getPlateBrakeTestObjectForClass1And2($scenario)
        );
    }

    /**
     * @Given /^I add gradient brake test data for (.*)$/
     */
    public function iAddGradientBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultData->addBrakeTestGradientClass1To2WithCustomData(
            $this->motTestData->getLast(),
            $this->getGradientBrakeTestObjectForClass1And2($scenario)
        );
    }

    /**
     * @Given /^I add floor brake test data for (.*)$/
     */
    public function iAddFloorBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultData->addBrakeTestFloorClass1To2WithCustomData(
            $this->motTestData->getLast(),
            $this->getFloorBrakeTestObjectForClass1And2($scenario)
        );
    }

    /**
     * @When the Tester adds a Class 3-7 Plate Brake Test
     */
    public function theTesterAddsAClass3to7PlateBrakeTest()
    {
        $this->brakeTestResultData->addBrakeTestPlateClass3to7($this->motTestData->getLast());
    }

    /**
     * @Given /^the Tester adds a Class 3-7 Roller Brake Test Result with custom (.*)$/
     */
    public function theTesterAddsAClass3RollerBrakeTestResultWithCustom($scenario)
    {
        $this->brakeTestResultData->addBrakeTestRollerClass3To7WithCustomData(
            $this->motTestData->getLast(),
            $this->getRollerBrakeTestObjectForClass3To7($scenario)

        );
    }

    /**
     * @When I submit brake test results with all service brake controls under 30% efficiency and no wheels locked
     */
    public function iSubmitBrakeTestResultsWithAllServiceBrakeControlsUnderEfficiencyAndNoWheelsLocked()
    {
        $mot = $this->motTestData->getLast();

        $this->odometerReadingData->addMeterReading($mot, 1000, MeterReadingParams::KM);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->odometerReadingData->getLastResponse()->getStatusCode());

        $brakeTestResultBuilder = (new Class3To7BrakeTestResultsBuilder())
            ->withVehicleWeight(1000)
            ->withAllEqualServiceBrakeEffort(70)
            ->withAllEqualServiceBrakeWheelLocks(false);

        $this->brakeTestResult->addClass3To7BrakeTestResult(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber(),
            $brakeTestResultBuilder
        );
    }

    /**
     * @When I submit brake test results with all service brake controls under 30% efficiency and wheels locked
     */
    public function iSubmitBrakeTestResultsWithAllServiceBrakeControlsUnderEfficiencyAndWheelsLocked()
    {
        $mot = $this->motTestData->getLast();

        $this->odometerReadingData->addMeterReading($mot, 1000, MeterReadingParams::KM);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->odometerReadingData->getLastResponse()->getStatusCode());

        $brakeTestResultBuilder = (new Class3To7BrakeTestResultsBuilder())
            ->withVehicleWeight(1000)
            ->withAllEqualServiceBrakeEffort(70)
            ->withAllEqualServiceBrakeWheelLocks(true);

        $this->brakeTestResult->addClass3To7BrakeTestResult(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber(),
            $brakeTestResultBuilder
        );
    }

    /**
     * @When I submit brake test results with vehicle weight of :vehicleWeight and vehicle weight source of :vehicleWeightSource
     *
     * @param int    $vehicleWeight
     * @param string $vehicleWeightSource
     */
    public function iSubmitBrakeTestResultsWithVehicleWeightOfAndVehicleWeightSourceOf($vehicleWeight, $vehicleWeightSource)
    {
        $mot = $this->motTestData->getLast();

        $this->odometerReadingData->addMeterReading($mot, 1000, MeterReadingParams::KM);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->odometerReadingData->getLastResponse()->getStatusCode());

        $brakeTestResultBuilder = (new Class3To7BrakeTestResultsBuilder())
            ->withVehicleWeight($vehicleWeight)
            ->withVehicleWeightSource($vehicleWeightSource);

        $this->brakeTestResult->addClass3To7BrakeTestResult(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber(),
            $brakeTestResultBuilder
        );
    }

    /**
     * @Then /^the controlOne and controlTwo status should be (.*) (.*)$/
     */
    public function theControlOneAndControlTwoStatusShouldBe($expectedControl1Pass, $expectedControl2Pass)
    {
        $mot = $this->motTestData->getLast();

        $actualControl1Pass = $mot->getBrakeTestResult()[BrakeTestResultParams::CONTROL_1_EFFICIENCY_PASS];
        $actualControl2Pass = $mot->getBrakeTestResult()[BrakeTestResultParams::CONTROL_2_EFFICIENCY_PASS];

        PHPUnit::assertEquals($expectedControl1Pass, $actualControl1Pass);
        PHPUnit::assertEquals($expectedControl2Pass, $actualControl2Pass);
    }

    private function getRollerBrakeTestObjectForClass3To7($scenario) {
        switch ($scenario) {
            case "class4.roller.valid.high":
                return new RollerBrakeTestClass3To7([
                    'serviceBrake1Data' => [
                        'effortNearsideAxle1' => 9999,
                        'effortOffsideAxle1' => 9999,
                        'lockNearsideAxle1' => false,
                        'lockOffsideAxle1' => true,
                        'effortNearsideAxle2' => 9999,
                        'effortOffsideAxle2' => 9999,
                        'lockNearsideAxle2' => true,
                        'lockOffsideAxle2' => false,
                    ],
                    'parkingBrakeEffortSingle' => 9999,
                    'parkingBrakeLockSingle' => false,
                    'parkingBrakeEffortNearside' => 9999,
                    'parkingBrakeEffortOffside' => 9999,
                    'parkingBrakeLockNearside' => false,
                    'parkingBrakeLockOffside' => false,
                    'serviceBrake1TestType' => 'rollr',
                    'serviceBrake2TestType' => null,
                    'parkingBrakeTestType' => 'rollr',
                    'weightType' => 'vsi',
                    'vehicleWeight' => 1000,
                    'brakeLineType' => 'dual',
                    'numberOfAxles' => 2,
                    'parkingBrakeNumberOfAxles' => 1,
                    'positionOfSingleWheel' => null,
                    'parkingBrakeWheelsCount' => null,
                    'serviceBrakeControlsCount' => null,
                    'vehiclePurposeType' => null,
                    'serviceBrakeIsSingleLine' => true,
                    'weightIsUnladen' => false,
                    'isCommercialVehicle' => false,
                ]);
            case "class4.roller.valid.low":
                return new RollerBrakeTestClass3To7([
                    'serviceBrake1Data' => [
                        'effortNearsideAxle1' => 23,
                        'effortOffsideAxle1' => 23,
                        'lockNearsideAxle1' => true,
                        'lockOffsideAxle1' => true,
                        'effortNearsideAxle2' => 23,
                        'effortOffsideAxle2' => 23,
                        'lockNearsideAxle2' => true,
                        'lockOffsideAxle2' => true,
                    ],
                    'parkingBrakeEffortSingle' => 23,
                    'parkingBrakeLockSingle' => true,
                    'parkingBrakeEffortNearside' => 23,
                    'parkingBrakeEffortOffside' => 23,
                    'parkingBrakeLockNearside' => true,
                    'parkingBrakeLockOffside' => true,
                    'serviceBrake1TestType' => 'rollr',
                    'serviceBrake2TestType' => null,
                    'parkingBrakeTestType' => 'rollr',
                    'weightType' => 'vsi',
                    'vehicleWeight' => 1000,
                    'brakeLineType' => 'dual',
                    'numberOfAxles' => 2,
                    'parkingBrakeNumberOfAxles' => 1,
                    'positionOfSingleWheel' => null,
                    'parkingBrakeWheelsCount' => null,
                    'serviceBrakeControlsCount' => null,
                    'vehiclePurposeType' => null,
                    'serviceBrakeIsSingleLine' => true,
                    'weightIsUnladen' => false,
                    'isCommercialVehicle' => false,
                ]);
            case "class4.roller.invalid.low":
                return new RollerBrakeTestClass3To7([
                    'serviceBrake1Data' => [
                        'effortNearsideAxle1' => 0,
                        'effortOffsideAxle1' => 0,
                        'lockNearsideAxle1' => true,
                        'lockOffsideAxle1' => true,
                        'effortNearsideAxle2' => 0,
                        'effortOffsideAxle2' => 0,
                        'lockNearsideAxle2' => true,
                        'lockOffsideAxle2' => true,
                    ],
                    'parkingBrakeEffortSingle' => 0,
                    'parkingBrakeLockSingle' => false,
                    'parkingBrakeEffortNearside' => 0,
                    'parkingBrakeEffortOffside' => 0,
                    'parkingBrakeLockNearside' => false,
                    'parkingBrakeLockOffside' => false,
                    'serviceBrake1TestType' => 'rollr',
                    'serviceBrake2TestType' => null,
                    'parkingBrakeTestType' => 'rollr',
                    'weightType' => 'vsi',
                    'vehicleWeight' => 1000,
                    'brakeLineType' => 'dual',
                    'numberOfAxles' => 2,
                    'parkingBrakeNumberOfAxles' => 1,
                    'positionOfSingleWheel' => null,
                    'parkingBrakeWheelsCount' => null,
                    'serviceBrakeControlsCount' => null,
                    'vehiclePurposeType' => null,
                    'serviceBrakeIsSingleLine' => false,
                    'weightIsUnladen' => false,
                    'isCommercialVehicle' => false,
                ]);
            case "class4.roller.invalid.high":
                return new RollerBrakeTestClass3To7([
                    'serviceBrake1Data' => [
                        'effortNearsideAxle1' => 100,
                        'effortOffsideAxle1' => 100,
                        'lockNearsideAxle1' => false,
                        'lockOffsideAxle1' => false,
                        'effortNearsideAxle2' => 100,
                        'effortOffsideAxle2' => 100,
                        'lockNearsideAxle2' => false,
                        'lockOffsideAxle2' => false,
                    ],
                    'parkingBrakeEffortSingle' => 1500,
                    'parkingBrakeLockSingle' => false,
                    'parkingBrakeEffortNearside' => 1500,
                    'parkingBrakeEffortOffside' => 1500,
                    'parkingBrakeLockNearside' => false,
                    'parkingBrakeLockOffside' => false,
                    'serviceBrake1TestType' => 'rollr',
                    'serviceBrake2TestType' => null,
                    'parkingBrakeTestType' => 'rollr',
                    'weightType' => 'vsi',
                    'vehicleWeight' => 1000,
                    'brakeLineType' => 'dual',
                    'numberOfAxles' => 2,
                    'parkingBrakeNumberOfAxles' => 1,
                    'positionOfSingleWheel' => null,
                    'parkingBrakeWheelsCount' => null,
                    'serviceBrakeControlsCount' => null,
                    'vehiclePurposeType' => null,
                    'serviceBrakeIsSingleLine' => false,
                    'weightIsUnladen' => false,
                    'isCommercialVehicle' => false,
                ]);
            default:
                throw new InvalidArgumentException;
        }
    }

    private function getRollerBrakeTestObjectForClass1And2($scenario){
        switch ($scenario){
            case "class1.roller.valid.high":
                return [
                    "control1EffortFront"    => 9999,
                    "control1EffortRear"     => 9999,
                    "control1EffortSidecar"  => 9999,
                    "control2EffortFront"    => 9999,
                    "control2EffortRear"     => 9999,
                    "control2EffortSidecar"  => 9999,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 9999,
                    "vehicleWeightRear"      => 9999,
                    "riderWeight"            => 80,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.valid.low":
                return [
                    "control1EffortFront"    => 23,
                    "control1EffortRear"     => 23,
                    "control1EffortSidecar"  => 23,
                    "control2EffortFront"    => 23,
                    "control2EffortRear"     => 23,
                    "control2EffortSidecar"  => 23,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 23,
                    "vehicleWeightRear"      => 23,
                    "riderWeight"            => 23,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.invalid.high":
                return [
                    "control1EffortFront"    => 50,
                    "control1EffortRear"     => 50,
                    "control1EffortSidecar"  => 50,
                    "control2EffortFront"    => 50,
                    "control2EffortRear"     => 50,
                    "control2EffortSidecar"  => 50,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.invalid.low":
                return [
                    "control1EffortFront"    => 3,
                    "control1EffortRear"     => 3,
                    "control1EffortSidecar"  => 3,
                    "control2EffortFront"    => 3,
                    "control2EffortRear"     => 3,
                    "control2EffortSidecar"  => 3,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.locks.locked":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => true,
                    "control1LockRear"       => true,
                    "control2LockFront"      => true,
                    "control2LockRear"       => true,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.locks.ctrl1Pass":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => true,
                    "control1LockRear"       => false,
                    "control2LockFront"      => true,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.roller.locks.ctrl2Pass":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => false,
                    "control1LockRear"       => true,
                    "control2LockFront"      => false,
                    "control2LockRear"       => true,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            default:
                Throw new InvalidArgumentException();
        }
    }

    private function getPlateBrakeTestObjectForClass1And2($scenario){
        switch ($scenario){
            case "class1.plate.valid.high":
                return [
                    "control1EffortFront"    => 9999,
                    "control1EffortRear"     => 9999,
                    "control1EffortSidecar"  => 9999,
                    "control2EffortFront"    => 9999,
                    "control2EffortRear"     => 9999,
                    "control2EffortSidecar"  => 9999,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 9999,
                    "vehicleWeightRear"      => 9999,
                    "riderWeight"            => 80,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.valid.low":
                return [
                    "control1EffortFront"    => 23,
                    "control1EffortRear"     => 23,
                    "control1EffortSidecar"  => 23,
                    "control2EffortFront"    => 23,
                    "control2EffortRear"     => 23,
                    "control2EffortSidecar"  => 23,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 23,
                    "vehicleWeightRear"      => 23,
                    "riderWeight"            => 23,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.invalid.high":
                return [
                    "control1EffortFront"    => 50,
                    "control1EffortRear"     => 50,
                    "control1EffortSidecar"  => 50,
                    "control2EffortFront"    => 50,
                    "control2EffortRear"     => 50,
                    "control2EffortSidecar"  => 50,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.invalid.low":
                return [
                    "control1EffortFront"    => 3,
                    "control1EffortRear"     => 3,
                    "control1EffortSidecar"  => 3,
                    "control2EffortFront"    => 3,
                    "control2EffortRear"     => 3,
                    "control2EffortSidecar"  => 3,
                    "control1LockFront"      => false,
                    "control1LockRear"       => false,
                    "control2LockFront"      => false,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.locks.locked":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => true,
                    "control1LockRear"       => true,
                    "control2LockFront"      => true,
                    "control2LockRear"       => true,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.locks.ctrl1Pass":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => true,
                    "control1LockRear"       => false,
                    "control2LockFront"      => true,
                    "control2LockRear"       => false,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            case "class1.plate.locks.ctrl2Pass":
                return [
                    "control1EffortFront"    => 0,
                    "control1EffortRear"     => 0,
                    "control1EffortSidecar"  => 0,
                    "control2EffortFront"    => 0,
                    "control2EffortRear"     => 0,
                    "control2EffortSidecar"  => 0,
                    "control1LockFront"      => false,
                    "control1LockRear"       => true,
                    "control2LockFront"      => false,
                    "control2LockRear"       => true,
                    "vehicleWeightFront"     => 400,
                    "vehicleWeightRear"      => 400,
                    "riderWeight"            => 78,
                    "isSideCarAttached"      => 0,
                    "sidecarWeight"          => 0
                ];
            default:
                Throw new InvalidArgumentException();
        }
    }

    private function getFloorBrakeTestObjectForClass1And2($scenario)
    {
        switch ($scenario) {
            case "class1.floor.valid.high":
                return [
                    "control1Effort" => 9999,
                    "control2Effort" => 9999,
                    "vehicleWeightFront" => 9999,
                    "vehicleWeightRear" => 9999,
                    "control1LockFront" => false,
                    "control2LockFront" => false,
                    "riderWeight" => 80,
                    "sidecarWeight" => 0
                ];
            case "class1.floor.valid.low":
                return [
                    "control1Effort" => 1,
                    "control2Effort" => 1,
                    "vehicleWeightFront" => 1,
                    "vehicleWeightRear" => 1,
                    "control1LockFront" => false,
                    "control2LockFront" => false,
                    "riderWeight" => 1,
                    "sidecarWeight" => 0
                ];
            case "class1.floor.invalid.high":
                return [
                    "control1Effort" => 500,
                    "control2Effort" => 500,
                    "vehicleWeightFront" => 9999,
                    "vehicleWeightRear" => 9999,
                    "control1LockFront" => false,
                    "control2LockFront" => false,
                    "riderWeight" => 80,
                    "sidecarWeight" => 0
                ];
            case "class1.floor.invalid.low":
                return [
                    "control1Effort" => 4,
                    "control2Effort" => 4,
                    "vehicleWeightFront" => 19,
                    "vehicleWeightRear" => 19,
                    "control1LockFront" => false,
                    "control2LockFront" => false,
                    "riderWeight" => 10,
                    "sidecarWeight" => 1
                ];
            case "class1.floor.valid.allLocks":
                return [
                    "control1Effort" => 0,
                    "control2Effort" => 0,
                    "control1LockFront" => true,
                    "control2LockFront" => true,
                    "vehicleWeightFront" => 400,
                    "vehicleWeightRear" => 400,
                    "riderWeight" => 10,
                    "sidecarWeight" => 0
                ];
            case "class1.floor.valid.oneLock":
                return [
                    "control1Effort" => 0,
                    "control2Effort" => 0,
                    "control1LockFront" => true,
                    "control2LockFront" => false,
                    "vehicleWeightFront" => 400,
                    "vehicleWeightRear" => 400,
                    "riderWeight" => 10,
                    "sidecarWeight" => 0
                ];
            default:
                throw new InvalidArgumentException;
        }
    }

    private function getGradientBrakeTestObjectForClass1And2($scenario){
        switch ($scenario){
            case "class1.gradient.valid.high":
                return [
                    "control1Above30" => true,
                    "control2Above30" => true,
                    "control1Below25" => false,
                    "control2Below25" => false
                ];
            case "class1.gradient.valid.low":
                return [
                    "control1Above30" => true,
                    "control2Above30" => false,
                    "control1Below25" => false,
                    "control2Below25" => false
                ];
            case "class1.gradient.invalid.high":
                return [
                    "control1Above30" => false,
                    "control2Above30" => false,
                    "control1Below25" => true,
                    "control2Below25" => true
                ];
            case "class1.gradient.invalid.low":
                return [
                    "control1Above30" => false,
                    "control2Above30" => false,
                    "control1Below25" => false,
                    "control2Below25" => false
                ];
            default:
                throw new InvalidArgumentException;
        }
    }

    private function getDecelerometerBrakeTestDataForClass1To2($scenario)
    {
        switch ($scenario) {
            case "class1.decelerometer.valid.high":
                return [
                    'control1BrakeEfficiency' => 9999,
                    'control2BrakeEfficiency' => 9999
                ];
            case "class1.decelerometer.valid.low":
                return [
                    'control1BrakeEfficiency' => 30,
                    'control2BrakeEfficiency' => 30
                ];
            case "class1.decelerometer.invalid.high":
                return [
                    'control1BrakeEfficiency' => 29,
                    'control2BrakeEfficiency' => 29
                ];
            case "class1.decelerometer.invalid.low":
                return [
                    'control1BrakeEfficiency' => 2,
                    'control2BrakeEfficiency' => 3
                ];
            default:
                Throw new InvalidArgumentException();
        }
    }

    private function getDecelerometerBrakeTestDataForClass3To7($scenario)
    {
        switch ($scenario) {
            case "class4.decelerometer.valid.high":
                return [
                    "serviceBrake1Efficiency" => 100,
                    "parkingBrakeEfficiency" => 100,
                    "isCommercialVehicle" => false
                ];
            case "class4.decelerometer.valid.low":
                return [
                    "serviceBrake1Efficiency" => 50,
                    "parkingBrakeEfficiency" => 16,
                    "isCommercialVehicle" => false
                ];
            case "class4.decelerometer.invalid.high":
                return [
                    "serviceBrake1Efficiency" => 49,
                    "parkingBrakeEfficiency" => 49,
                    "isCommercialVehicle" => false
                ];
            case "class4.decelerometer.invalid.low":
                return [
                    "serviceBrake1Efficiency" => 49,
                    "parkingBrakeEfficiency" => 15,
                    "isCommercialVehicle" => false
                ];
            case "class4.decelerometer.commercial.valid.high":
                return [
                    "serviceBrake1Efficiency" => 58,
                    "parkingBrakeEfficiency" => 30,
                    "isCommercialVehicle" => true
                ];
            case "class4.decelerometer.commercial.valid.low":
                return [
                    "serviceBrake1Efficiency" => 57,
                    "parkingBrakeEfficiency" => 30,
                    "isCommercialVehicle" => true
                ];
            case "class4.decelerometer.goods.valid.high":
                return [
                    "serviceBrake1Efficiency" => 50,
                    "parkingBrakeEfficiency" => 30,
                    "isCommercialVehicle" => false
                ];
            case "class4.decelerometer.goods.invalid.low":
                return [
                    "serviceBrake1Efficiency" => 49,
                    "parkingBrakeEfficiency" => 30,
                    "isCommercialVehicle" => false
                ];
            default:
                throw new InvalidArgumentException;
        }
    }
}
