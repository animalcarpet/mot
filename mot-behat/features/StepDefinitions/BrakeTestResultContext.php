<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\RollerBrakeTestClass3To7;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;

class BrakeTestResultContext implements Context
{
    /**
     * @var BrakeTestResult
     */
    private $brakeTestResult;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var MotTestContext
     */
    private $motTestContext;

    /**
     * @var Response
     */
    private $brakeTestResultResponse;

    /**
     * @param BrakeTestResult $brakeTestResult
     */
    public function __construct(BrakeTestResult $brakeTestResult)
    {
        $this->brakeTestResult = $brakeTestResult;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
    }

    /**
     * @Given the Tester adds a Class 3-7 Roller Brake Test Result
     */
    public function theTesterAddsAClass3To7RollerBrakeTestResult()
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestRollerClass3To7(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber()
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given the Tester adds a Class 1-2 Roller Brake Test Result
     */
    public function theTesterAddsAClass1To2RollerBrakeTestResult()
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestForRollerClass1To2(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber()
        );
    }

    /**
     * @Given the Tester adds a Class 3-7 Decelerometer Brake Test
     */
    public function theTesterAddsAClass3To7DecelerometerBrakeTest()
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestDecelerometerClass3To7(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber()
        );

        $this->assertResponseIsCorrect($this->brakeTestResultResponse, 'Confirm Deceleromter was added');
    }

    /**
     * @Given /^the Tester adds a Class 3-7 Decelerometer Brake Test Result with custom (.*)$/
     */
    public function theTesterAddsAClass3To7DecelerometerBrakeTestWithCustomData($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestDecelerometerClass3To7WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getDecelerometerBrakeTestDataForClass3To7($scenario)
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given the Tester adds a Class 1-2 Decelerometer Brake Test
     */
    public function theTesterAddsAClass1DecelerometerBrakeTest()
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestDecelerometerClass1To2(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber()
        );

        $this->assertResponseIsCorrect($this->brakeTestResultResponse, 'Incorrect status code when adding Decelerometer brake test.');
    }

    /**
     * @Given /^the Tester adds a Class 1-2 Decelerometer Brake Test with custom brake data (.*)$/
     */
    public function theTesterAddsAClass1DecelerometerBrakeTestWithCustomBrakeData($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestDecelerometerClass1To2WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getDecelerometerBrakeTestDataForClass1To2($scenario)
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given /^I add roller brake test data for (.*)$/
     */
    public function iAddRollerBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestForRollerClass1To2WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getRollerBrakeTestObjectForClass1And2($scenario)
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given /^I add plate brake test data for (.*)$/
     */
    public function iAddPlateBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestForPlateClass1To2WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getPlateBrakeTestObjectForClass1And2($scenario)
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given /^I add gradient brake test data for (.*)$/
     */
    public function iAddGradientBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestGradientClass1To2WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getGradientBrakeTestObjectForClass1And2($scenario)
        );

        $this->assertResponseIsCorrect($this->brakeTestResultResponse,
            'Incorrect status code when adding Gradient brake test.');
    }

    /**
     * @Given /^I add floor brake test data for (.*)$/
     */
    public function iAddFloorBrakeTestDataFor($scenario)
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestFloorClass1To2WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $this->getFloorBrakeTestObjectForClass1And2($scenario)
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @When the Tester adds a Class 3-7 Plate Brake Test
     */
    public function theTesterAddsAClass3to7PlateBrakeTest()
    {
        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestPlateClass3to7(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber()
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @Given /^the Tester adds a Class 3-7 Roller Brake Test Result with custom (.*)$/
     */
    public function theTesterAddsAClass3RollerBrakeTestResultWithCustom($scenario)
    {
        $rollerBrakeTestObject = $this->getRollerBrakeTestObjectForClass3To7($scenario);

        $this->brakeTestResultResponse = $this->brakeTestResult->addBrakeTestRollerClass3To7WithCustomData(
            $this->sessionContext->getCurrentAccessToken(),
            $this->motTestContext->getMotTestNumber(),
            $rollerBrakeTestObject
        );
        $this->assertResponseIsCorrect($this->brakeTestResultResponse);
    }

    /**
     * @param Response $response
     */
    private function assertResponseIsCorrect($response, $message = 'Brake test response code is not 200')
    {
        \PHPUnit_Framework_Assert::assertEquals(200, $response->getStatusCode(), $message);
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
