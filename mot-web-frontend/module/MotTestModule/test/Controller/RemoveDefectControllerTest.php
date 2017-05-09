<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Class RemoveDefectControllerTest.
 */
class RemoveDefectControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var DefectsJourneyContextProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    private $defectsJourneyContextProviderMock;

    /**
     * @var DefectsJourneyUrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $defectsJourneyUrlGeneratorMock;

    /**
     * @var array[]
     */
    private $reasonsForRejection;

    protected $mockMotTestServiceClient;

    protected function setUp()
    {
        $this->defectsJourneyContextProviderMock = $this
            ->getMockBuilder(DefectsJourneyContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->defectsJourneyUrlGeneratorMock = $this
            ->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $this->serviceManager->setAllowOverride(true);

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new RemoveDefectController($this->defectsJourneyContextProviderMock, $this->defectsJourneyUrlGeneratorMock)
        );

        parent::setUp();
    }

    private function getMockMotTestServiceClient()
    {
        if ($this->mockMotTestServiceClient == null) {
            $this->mockMotTestServiceClient = XMock::of(MotTestService::class);
        }

        return $this->mockMotTestServiceClient;
    }

    /**
     * Test that the Remove a Defect page loads correctly.
     */
    public function testRemoveActionWithGetMethod()
    {
        $motTestNumber = 1;
        $defectId = 1;

        $this->withFailuresPrsAndAdvisories(1, 2, 3);
        $restClientMock = $this->getRestClientMockForServiceManager();

        $testMotTestData = $this->getMotTestDataClass4();

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'identifiedDefectId' => $defectId,
        ];

        $this->getResultForAction2('get', 'remove', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test that POSTing to the Remove action removes a defect.
     */
    public function testRemoveActionWithPostMethod()
    {
        $motTestNumber = 1;
        $defectId = 1;

        $this->withFailuresPrsAndAdvisories(1, 2, 3);
        $restClientMock = $this->getRestClientMockForServiceManager();
        $testMotTestData = $this->getMotTestDataClass4();

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $restClientMock->expects($this->at(0))
            ->method('get')
            ->willReturn(['data' => $this->getDefectDto()]);

        $restClientMock->expects($this->once())
            ->method('delete')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId));

        $this->defectsJourneyUrlGeneratorMock->method('goBack')->willReturn('user-home');

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'identifiedDefectId' => $defectId,
        ];

        $this->getResultForAction2('post', 'remove', $routeParams);

        // We should get redirected...
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * @param array $fail
     * @param array $prs
     * @param array $advisory
     */
    private function setUpReasonForRejectionsArrayForMotTestMock(array $fail, array $prs, array $advisory)
    {
        $this->reasonsForRejection = [
            'FAIL' => $fail,
            'PRS' => $prs,
            'ADVISORY' => $advisory,
        ];
    }

    /**
     * @param int $failures
     * @param int $prs
     * @param int $advisories
     *
     * @return $this
     */
    private function withFailuresPrsAndAdvisories($failures, $prs, $advisories)
    {
        $failArray = [];
        $prsArray = [];
        $advisoryArray = [];

        $defectId = 1;

        for ($i = 0; $i < $failures; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::FAIL;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';
            $rfr['onOriginalTest'] = '';
            $rfr['generated'] = false;
            $rfr['markedAsRepaired'] = false;

            $failArray[] = $rfr;
            ++$defectId;
        }

        for ($i = 0; $i < $prs; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::PRS;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';
            $rfr['onOriginalTest'] = '';
            $rfr['generated'] = false;
            $rfr['markedAsRepaired'] = false;

            $prsArray[] = $rfr;

            ++$defectId;
        }

        for ($i = 0; $i < $advisories; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::ADVISORY;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';
            $rfr['onOriginalTest'] = '';
            $rfr['generated'] = false;
            $rfr['markedAsRepaired'] = false;

            $advisoryArray[] = $rfr;
            ++$defectId;
        }

        $this->setUpReasonForRejectionsArrayForMotTestMock($failArray, $prsArray, $advisoryArray);

        return $this;
    }

    /**
     * @return DefectDto
     */
    private function getDefectDto()
    {
        $defectDto = new DefectDto();

        $defectDto->setId(1);
        $defectDto->setParentCategoryId(0);
        $defectDto->setDescription('');
        $defectDto->setDefectBreadcrumb('');
        $defectDto->setAdvisory('');
        $defectDto->setInspectionManualReference('');
        $defectDto->setAdvisory(false);
        $defectDto->setPrs(false);
        $defectDto->setFailure(false);

        return $defectDto;
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
      "inspectionManualReference" : "inspectionManualReference",
      "markedAsRepaired" : "FALSE"
    } ]
  },
  "statusCode" : "ACTIVE",
  "testTypeCode" : "NT",
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
}
