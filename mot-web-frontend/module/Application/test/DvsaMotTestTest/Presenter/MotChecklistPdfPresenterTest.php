<?php

namespace Application\test\DvsaMotTestTest\Presenter;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Presenter\MotChecklistPdfPresenter;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use DvsaMotTestTest\TestHelper\Fixture;


class MotChecklistPdfPresenterTest extends \PHPUnit_Framework_TestCase
{
    /** @var $featureToggles FeatureToggles|\PHPUnit_Framework_MockObject_MockObject  */
    private $featureToggles;
    /** @var $officialVehicleWeightSpec OfficialWeightSourceForVehicle|\PHPUnit_Framework_MockObject_MockObject */
    private $officialVehicleWeightSpec;

    /** @var $presenter MotChecklistPdfPresenter */
    private $presenter;
    /** @var $identity MotFrontendIdentityInterface|\PHPUnit_Framework_MockObject_MockObject  */
    private $identity;

    /** @var $vehicle DvsaVehicle */
    private $vehicle;

    /** @var $testDto MotTest */
    private $testDto;

    public function setUp()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->officialVehicleWeightSpec = XMock::of(OfficialWeightSourceForVehicle::class);

        $this->presenter = new MotChecklistPdfPresenter($this->featureToggles, $this->officialVehicleWeightSpec);

        $this->identity = XMock::of(MotFrontendIdentityInterface::class);
        $this->presenter->setIdentity($this->identity);
    }

    /**
     * @dataProvider dataProviderTestFieldsAreDifferentForClass1And2
     *
     * @param int $classCode
     * @param int $fieldCount
     *
     * @throws \Exception
     */
    public function testFieldsAreDifferentForClass1And2($classCode, $fieldCount)
    {
        $this->setUpPresenterBasedOnClass($classCode);
        $this->assertCount($fieldCount, $this->presenter->getDataFields());
    }

    /**
     * @dataProvider dataProviderFeatureToggleStatus
     *
     * @param string $classCode
     * @param bool $featureToggleValue
     */
    public function testPickVehicleWeight_withFeatureToggle($classCode, $featureToggleValue)
    {
        $this->featureToggles
            ->expects($this->any())
            ->method("isEnabled")
            ->with(FeatureToggle::VEHICLE_WEIGHT_FROM_VEHICLE)
            ->willReturn($featureToggleValue);

        $this->setUpPresenterBasedOnClass($classCode);

        $this->officialVehicleWeightSpec
            ->expects($featureToggleValue === true ? $this->once() : $this->never())
            ->method('isSatisfiedBy');

        $this->presenter->pickVehicleWeight();
    }


    public function dataProviderFeatureToggleStatus()
    {
        return [
            [VehicleClassCode::CLASS_4, true],

            [VehicleClassCode::CLASS_1, false],
            [VehicleClassCode::CLASS_4, false],
        ];
    }

    public function dataProviderTestFieldsAreDifferentForClass1And2()
    {
        return [
            [VehicleClassCode::CLASS_1, 10],
            [VehicleClassCode::CLASS_2, 10],
            [VehicleClassCode::CLASS_3, 11],
            [VehicleClassCode::CLASS_4, 11],
            [VehicleClassCode::CLASS_5, 11],
            [VehicleClassCode::CLASS_7, 11],
        ];
    }

    /**
     * @return MotTest
     */
    private function getMotTestDataClass4()
    {
        $testDataJSON = '{
    "id" : 1,
    "brakeTestResult" : {
    "id" : 999888003,
    "generalPass" : false,
    "isLatest" : true,
    "commercialVehicle" : true,
    "numberOfAxles" : 2,
    "parkingBrakeEfficiency" : 30,
    "parkingBrakeEfficiencyPass" : false,
    "parkingBrakeEffortNearside" : 31,
    "parkingBrakeEffortOffside" : 32,
    "parkingBrakeEffortSecondaryNearside" : 33,
    "parkingBrakeEffortSecondaryOffside" : 34,
    "parkingBrakeEffortSingle" : 35,
    "parkingBrakeImbalance" : 36,
    "parkingBrakeImbalancePass" : true,
    "parkingBrakeLockNearside" : false,
    "parkingBrakeLockOffside" : true,
    "parkingBrakeLockPercent" : 37,
    "parkingBrakeLockSecondaryNearside" : true,
    "parkingBrakeLockSecondaryOffside" : false,
    "parkingBrakeLockSingle" : false,
    "parkingBrakeNumberOfAxles" : 1,
    "parkingBrakeSecondaryImbalance" : 38,
    "parkingBrakeTestType" : "GRADT",
    "serviceBrake1Data" : {
      "id" : 999888009,
      "effortNearsideAxel1" : 50,
      "effortNearsideAxel2" : 51,
      "effortNearsideAxel3" : 52,
      "effortOffsideAxel1" : 53,
      "effortOffsideAxel2" : 54,
      "effortOffsideAxel3" : 55,
      "effortSingle" : 56,
      "imbalanceAxle1" : 58,
      "imbalanceAxle2" : 59,
      "imbalanceAxle3" : 60,
      "imbalancePass" : true,
      "lockNearsideAxle1" : false,
      "lockNearsideAxle2" : true,
      "lockNearsideAxle3" : false,
      "lockOffsideAxle1" : true,
      "lockOffsideAxle2" : false,
      "lockOffsideAxle3" : true,
      "lockPercent" : 68,
      "lockSingle" : false
    },
    "serviceBrake1Efficiency" : 39,
    "serviceBrake1EfficiencyPass" : true,
    "serviceBrake1TestType" : "PLATE",
    "serviceBrake2Data" : {
      "id" : 999888009,
      "effortNearsideAxel1" : 50,
      "effortNearsideAxel2" : 51,
      "effortNearsideAxel3" : 52,
      "effortOffsideAxel1" : 53,
      "effortOffsideAxel2" : 54,
      "effortOffsideAxel3" : 55,
      "effortSingle" : 56,
      "imbalanceAxle1" : 58,
      "imbalanceAxle2" : 59,
      "imbalanceAxle3" : 60,
      "imbalancePass" : true,
      "lockNearsideAxle1" : false,
      "lockNearsideAxle2" : true,
      "lockNearsideAxle3" : false,
      "lockOffsideAxle1" : true,
      "lockOffsideAxle2" : false,
      "lockOffsideAxle3" : true,
      "lockPercent" : 68,
      "lockSingle" : false
    },
    "serviceBrake2Efficiency" : 40,
    "serviceBrake2EfficiencyPass" : true,
    "serviceBrake2TestType" : "FLOOR",
    "serviceBrakeIsSingleLine" : true,
    "singleInFront" : false,
    "vehicleWeight" : 5000,
    "weightIsUnladen" : true,
    "weightType" : "VSI"
  },
  "completedDate" : "2015-12-18",
  "expiryDate" : "2015-12-18",
  "issuedDate" : "2015-12-18",
  "startedDate" : "2015-12-18",
  "motTestNumber" : "1",
  "reasonForTerminationComment" : "comment",
  "reasonsForRejection" : {
    "ADVISORY" : [ {
      "id" : 1,
      "type" : "ADVISORY",
      "locationLateral" : "locationLateral",
      "locationLongitudinal" : "locationLongitudinal",
      "locationVertical" : "locationVertical",
      "comment" : "comment",
      "failureDangerous" : false,
      "generated" : false,
      "customDescription" : "customDescription",
      "onOriginalTest" : false,
      "rfrId" : 1,
      "name" : "advisory",
      "nameCy" : "advisory",
      "testItemSelectorDescription" : "testItemSelectorDescription",
      "testItemSelectorDescriptionCy" : null,
      "failureText" : "advisory",
      "failureTextCy" : "advisorycy",
      "testItemSelectorId" : 1,
      "inspectionManualReference" : "inspectionManualReference"
    } ]
  },
  "statusCode" : "ACTIVE",
  "testTypeCode" : "NORMAL",
  "tester" : {
    "id" : 1,
    "firstName" : "Joe",
    "middleName" : "John",
    "lastName" : "Bloggs"
  },
  "testerBrakePerformanceNotTested" : true,
  "hasRegistration" : true,
  "siteId" : 1,
  "vehicleId" : 1001,
  "vehicleVersion" : 1,
  "pendingDetails" : {
    "currentSubmissionStatus" : "PASSED",
    "issuedDate" : "2015-12-18",
    "expiryDate" : "2015-12-18"
  },
  "reasonForCancel" : {
    "id" : 1,
    "reason" : "reason",
    "reasonCy" : "reasonCy",
    "abandoned" : true,
    "isDisplayable" : true
  },
  "motTestOriginalNumber" : "12345",
  "prsMotTestNumber" : "123456",
  "odometerValue" : 1000,
  "odometerUnit" : "mi",
  "odometerResultType" : "OK"
}';

        return new MotTest(json_decode($testDataJSON));
    }

    /**
     * @return DvsaVehicle
     */
    protected function getVehicleTestDataClass4()
    {
        $testData = '{
  "id" : 1,
  "amendedOn" : "2015-12-18",
  "registration" : "DII4454",
  "vin" : "1M7GDM9AXKP042777",
  "emptyVrmReason" : null,
  "emptyVinReason" : null,
  "make" : {
    "id" : 1,
    "name" : "PORSCHE"
  },
  "model" : {
    "id" : 2,
    "name" : "BOXSTER"
  },
  "colour" : {
    "code" : "C",
    "name" : "Red"
  },
  "colourSecondary" : {
    "code" : "W",
    "name" : "Not Stated"
  },
  "countryOfRegistrationId" : 10,
  "fuelType" : {
    "code" : "PE",
    "name" : "Petrol"
  },
  "vehicleClass" : {
    "code" : "4",
    "name" : "4"
  },
  "bodyType" : "2 Door Saloon",
  "cylinderCapacity" : 1700,
  "transmissionType" : "Automatic",
  "firstRegistrationDate" : "2015-12-18",
  "firstUsedDate" : "2015-12-18",
  "manufactureDate" : "2015-12-18",
  "isNewAtFirstReg" : false,
  "isIncognito" : false,
  "weight" : 0,
  "version" : 1
}';

        return new DvsaVehicle(json_decode($testData));
    }

    /**
     * @return DvsaVehicle
     */
    protected function getVehicleTestDataClass1()
    {
        $testData = '{
  "id" : 1,
  "amendedOn" : "2015-12-18",
  "registration" : "DII4454",
  "vin" : "1M7GDM9AXKP042777",
  "emptyVrmReason" : null,
  "emptyVinReason" : null,
  "make" : {
    "id" : 1,
    "name" : "PORSCHE"
  },
  "model" : {
    "id" : 2,
    "name" : "BOXSTER"
  },
  "colour" : {
    "code" : "C",
    "name" : "Red"
  },
  "colourSecondary" : {
    "code" : "W",
    "name" : "Not Stated"
  },
  "countryOfRegistrationId" : 10,
  "fuelType" : {
    "code" : "PE",
    "name" : "Petrol"
  },
  "vehicleClass" : {
    "code" : "1",
    "name" : "1"
  },
  "bodyType" : "2 Door Saloon",
  "cylinderCapacity" : 1700,
  "transmissionType" : "Automatic",
  "firstRegistrationDate" : "2015-12-18",
  "firstUsedDate" : "2015-12-18",
  "manufactureDate" : "2015-12-18",
  "isNewAtFirstReg" : false,
  "isIncognito" : false,
  "weight" : 0,
  "version" : 1
}';

        return new DvsaVehicle(json_decode($testData));
    }

    public function getMotTestDataClass1()
    {
        $testDataJSON = '{
  "id" : 1,
  "brakeTestResult" : {
    "id" : 999888001,
    "generalPass" : false,
    "isLatest" : true,
    "brakeTestTypeCode" : "ROLLR",
    "control1BrakeEfficiency" : 54,
    "control1EfficiencyPass" : true,
    "control1EffortFront" : 55,
    "control1EffortRear" : 56,
    "control1EffortSidecar" : 57,
    "control1LockFront" : false,
    "control1LockPercent" : 21,
    "control1LockRear" : true,
    "control2BrakeEfficiency" : 22,
    "control2EfficiencyPass" : false,
    "control2EffortFront" : 31,
    "control2EffortRear" : 32,
    "control2EffortSidecar" : 32,
    "control2LockFront" : false,
    "control2LockPercent" : 91,
    "control2LockRear" : false,
    "gradientControl1BelowMinimum" : true,
    "gradientControl2BelowMinimum" : true,
    "riderWeight" : 60,
    "sidecarWeight" : 300,
    "vehicleWeightFront" : 400,
    "vehicleWeightRear" : 450
  },
  "completedDate" : "2015-12-18",
  "expiryDate" : "2015-12-18",
  "issuedDate" : "2015-12-18",
  "startedDate" : "2015-12-18",
  "motTestNumber" : "1",
  "reasonForTerminationComment" : "comment",
  "reasonsForRejection" : {
    "ADVISORY" : [ {
      "id" : 1,
      "type" : "ADVISORY",
      "locationLateral" : "locationLateral",
      "locationLongitudinal" : "locationLongitudinal",
      "locationVertical" : "locationVertical",
      "comment" : "comment",
      "failureDangerous" : false,
      "generated" : false,
      "customDescription" : "customDescription",
      "onOriginalTest" : false,
      "rfrId" : 1,
      "name" : "advisory",
      "nameCy" : "advisory",
      "testItemSelectorDescription" : "testItemSelectorDescription",
      "testItemSelectorDescriptionCy" : null,
      "failureText" : "advisory",
      "failureTextCy" : "advisorycy",
      "testItemSelectorId" : 1,
      "inspectionManualReference" : "inspectionManualReference"
    } ]
  },
  "statusCode" : "ACTIVE",
  "testTypeCode" : "NORMAL",
  "tester" : {
    "id" : 1,
    "firstName" : "Joe",
    "middleName" : "John",
    "lastName" : "Bloggs"
  },
  "testerBrakePerformanceNotTested" : true,
  "hasRegistration" : true,
  "siteId" : 1,
  "vehicleId" : 1001,
  "vehicleVersion" : 1,
  "pendingDetails" : {
    "currentSubmissionStatus" : "PASSED",
    "issuedDate" : "2015-12-18",
    "expiryDate" : "2015-12-18"
  },
  "reasonForCancel" : {
    "id" : 1,
    "reason" : "reason",
    "reasonCy" : "reasonCy",
    "abandoned" : true,
    "isDisplayable" : true
  },
  "motTestOriginalNumber" : "12345",
  "prsMotTestNumber" : "123456",
  "odometerValue" : 1000,
  "odometerUnit" : "mi",
  "odometerResultType" : "OK"
}
';

        return new MotTest(json_decode($testDataJSON));
    }

    private function getDvsaVehicleStub($class)
    {
        if($class < 3){
            return new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));
        }
        else {
            return new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));
        }
    }
    private function getMotTestStub($class)
    {
        if($class < 3){
            return new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        }
        else {
            return new MotTest(Fixture::getMotTestDataVehicleClass4(true));
        }
    }

    /**
     * @param $classCode
     */
    private function setUpPresenterBasedOnClass($classCode)
    {
        $this->vehicle = $this->getDvsaVehicleStub($classCode);
        $this->testDto = $this->getMotTestStub($classCode);

        $this->presenter->setMotTest($this->testDto);
        $this->presenter->setVehicle($this->vehicle);
    }
}
