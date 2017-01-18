<?php

namespace DvsaMotTest\Model;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass1And2;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;

/**
 * Unit tests for BrakeEffort
 */
class BrakeTestResultClass1And2Test extends \PHPUnit_Framework_TestCase
{
    public function test_getId_shouldReturnCorrectId()
    {
        $this->assertEquals('control1EffortFront', BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_FRONT);
        $this->assertEquals('control1LockFront', BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_FRONT);
        $this->assertEquals('control1EffortRear', BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_REAR);
        $this->assertEquals('control1LockRear', BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_REAR);
        $this->assertEquals('control1EffortSidecar', BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_SIDECAR);
        $this->assertEquals(
            'control1BrakeEfficiency',
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_BRAKE_EFFICIENCY
        );

        $this->assertEquals('control2EffortFront', BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_FRONT);
        $this->assertEquals('control2LockFront', BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_FRONT);
        $this->assertEquals('control2EffortRear', BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_REAR);
        $this->assertEquals('control2LockRear', BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_REAR);
        $this->assertEquals('control2EffortSidecar', BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_SIDECAR);
        $this->assertEquals(
            'control2BrakeEfficiency',
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_BRAKE_EFFICIENCY
        );

        $this->assertEquals('gradientControl1', BrakeTestResultClass1And2ViewModel::NAME_GRADIENT_CONTROL_1);
        $this->assertEquals('gradientControl2', BrakeTestResultClass1And2ViewModel::NAME_GRADIENT_CONTROL_2);
    }

    public function test_getControlNumberText_shouldReturnCorrectValue()
    {
        $this->assertEquals('one', BrakeTestConfigurationClass3AndAboveHelper::COUNT_ONE);
        $this->assertEquals('two', BrakeTestConfigurationClass3AndAboveHelper::COUNT_TWO);
    }

    public function test_getters()
    {
        $brakeTestResult = new BrakeTestResultClass1And2ViewModel(
            (new BrakeTestConfigurationClass1And2Dto()),
            $this->getBrakeTestResultClass1And2(),
            [
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_FRONT => 1,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_FRONT => 2,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_REAR => 3,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_REAR => 4,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_SIDECAR => 5,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_BRAKE_EFFICIENCY => 6,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_FRONT => 11,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_FRONT => 12,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_REAR => 13,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_REAR => 14,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_SIDECAR => 15,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_BRAKE_EFFICIENCY => 16,
            ]
        );

        $this->assertEquals(1, $brakeTestResult->getEffortFront1());
        $this->assertEquals(2, $brakeTestResult->getLockFront1());
        $this->assertEquals(3, $brakeTestResult->getEffortRear1());
        $this->assertEquals(4, $brakeTestResult->getLockRear1());
        $this->assertEquals(5, $brakeTestResult->getEffortSidecar1());
        $this->assertEquals(6, $brakeTestResult->getEfficiency1());

        $this->assertEquals(11, $brakeTestResult->getEffortFront2());
        $this->assertEquals(12, $brakeTestResult->getLockFront2());
        $this->assertEquals(13, $brakeTestResult->getEffortRear2());
        $this->assertEquals(14, $brakeTestResult->getLockRear2());
        $this->assertEquals(15, $brakeTestResult->getEffortSidecar2());
        $this->assertEquals(16, $brakeTestResult->getEfficiency2());
    }

    private function getBrakeTestResultClass1And2()
    {
        $testDataJSON = "{
  \"id\" : 1,
  \"brakeTestResult\" : {
    \"id\" : 999888001,
    \"generalPass\" : false,
    \"isLatest\" : true,
    \"brakeTestTypeCode\" : \"ROLLR\",
    \"control1BrakeEfficiency\" : 6,
    \"control1EfficiencyPass\" : true,
    \"control1EffortFront\" : 1,
    \"control1EffortRear\" : 3,
    \"control1EffortSidecar\" : 5,
    \"control1LockFront\" : 2,
    \"control1LockPercent\" : 21,
    \"control1LockRear\" : true,
    \"control2BrakeEfficiency\" : 16,
    \"control2EfficiencyPass\" : false,
    \"control2EffortFront\" : 11,
    \"control2EffortRear\" : 13,
    \"control2EffortSidecar\" : 15,
    \"control2LockFront\" : 12,
    \"control2LockPercent\" : 91,
    \"control2LockRear\" : 14,
    \"gradientControl1BelowMinimum\" : true,
    \"gradientControl2BelowMinimum\" : true,
    \"riderWeight\" : 60,
    \"sidecarWeight\" : 300,
    \"vehicleWeightFront\" : 400,
    \"vehicleWeightRear\" : 450
  },
  \"completedDate\" : \"2015-12-18\",
  \"expiryDate\" : \"2015-12-18\",
  \"issuedDate\" : \"2015-12-18\",
  \"startedDate\" : \"2015-12-18\",
  \"motTestNumber\" : \"1\",
  \"reasonForTerminationComment\" : \"comment\",
  \"reasonsForRejection\" : {
    \"ADVISORY\" : [ {
      \"id\" : 1,
      \"type\" : \"ADVISORY\",
      \"locationLateral\" : \"locationLateral\",
      \"locationLongitudinal\" : \"locationLongitudinal\",
      \"locationVertical\" : \"locationVertical\",
      \"comment\" : \"comment\",
      \"failureDangerous\" : false,
      \"generated\" : false,
      \"customDescription\" : \"customDescription\",
      \"onOriginalTest\" : false,
      \"rfrId\" : 1,
      \"name\" : \"advisory\",
      \"nameCy\" : \"advisory\",
      \"testItemSelectorDescription\" : \"testItemSelectorDescription\",
      \"testItemSelectorDescriptionCy\" : null,
      \"failureText\" : \"advisory\",
      \"failureTextCy\" : \"advisorycy\",
      \"testItemSelectorId\" : 1,
      \"inspectionManualReference\" : \"inspectionManualReference\"
    } ]
  },
  \"statusCode\" : \"P\",
  \"testTypeCode\" : \"NORMAL\",
  \"tester\" : {
    \"id\" : 1,
    \"firstName\" : \"Joe\",
    \"middleName\" : \"John\",
    \"lastName\" : \"Bloggs\"
  },
  \"testerBrakePerformanceNotTested\" : true,
  \"hasRegistration\" : true,
  \"siteId\" : 1,
  \"vehicleId\" : 1001,
  \"vehicleVersion\" : 1,
  \"pendingDetails\" : {
    \"currentSubmissionStatus\" : \"PASSED\",
    \"issuedDate\" : \"2015-12-18\",
    \"expiryDate\" : \"2015-12-18\"
  },
  \"reasonForCancel\" : {
    \"id\" : 1,
    \"reason\" : \"reason\",
    \"reasonCy\" : \"reasonCy\",
    \"abandoned\" : true,
    \"isDisplayable\" : true
  },
  \"motTestOriginalNumber\" : \"12345\",
  \"prsMotTestNumber\" : \"123456\",
  \"odometerValue\" : 1000,
  \"odometerUnit\" : \"mi\",
  \"odometerResultType\" : \"OK\"
}";
        $motTest = new MotTest(json_decode($testDataJSON));
        return new BrakeTestResultClass1And2($motTest->getBrakeTestResult());
    }
}
