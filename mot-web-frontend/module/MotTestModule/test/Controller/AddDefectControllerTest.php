<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use Application\Service\LoggedInUserManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\AddDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Model\ViewModel;

/**
 * Class AddDefectControllerTest.
 */
class AddDefectControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var LoggedInUserManager
     */
    private $loggedInUserManagerMock;

    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGeneratorMock;

    /**
     * @var DefectsJourneyContextProvider
     */
    private $defectsJourneyContextProviderMock;

    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $this->serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );
        $this->setServiceManager($this->serviceManager);

        $this->defectsJourneyUrlGeneratorMock = $this
            ->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->defectsJourneyContextProviderMock = $this
            ->getMockBuilder(DefectsJourneyContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setController(
            new AddDefectController($this->defectsJourneyUrlGeneratorMock, $this->defectsJourneyContextProviderMock)
        );
        $this->getController()->setServiceLocator($this->serviceManager);

        $this->loggedInUserManagerMock = XMock::of(
            LoggedInUserManager::class,
            []
        );

        $this->serviceManager->setService('LoggedInUserManager', $this->loggedInUserManagerMock);

        parent::setUp();
    }

    private function getMockMotTestServiceClient()
    {
        if ($this->mockMotTestServiceClient == null) {
            $this->mockMotTestServiceClient = XMock::of(MotTestService::class);
        }
        return $this->mockMotTestServiceClient;
    }

    private function getMockVehicleServiceClient()
    {
        if ($this->mockVehicleServiceClient == null) {
            $this->mockVehicleServiceClient = XMock::of(VehicleService::class);
        }
        return $this->mockVehicleServiceClient;
    }

    /**
     * Test the addAction in the AddDefectController without post data.
     */
    public function testAddDefect()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService([PermissionInSystem::RFR_LIST]);

        $motTestNumber = 1;
        $categoryId = 304;
        $defectId = 502;
        $type = 'advisory';

        $testMotTestData = $this->getMotTestDataClass4();

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->once())
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => $defectId,
            'type' => $type,
        ];

        $this->getResultForAction('add', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test the addAction in the AddDefectController with post data.
     *
     * @dataProvider testValidDefectDataProvider
     *
     * @param $defectId
     * @param $type
     * @param $locationLateral
     * @param $locationLongitudinal
     * @param $locationVertical
     * @param $additionalInformation
     * @param $failureDangerous
     */
    public function testAddDefectWithValidPostData($defectId, $type, $locationLateral, $locationLongitudinal,
                                                   $locationVertical, $additionalInformation, $failureDangerous)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService([PermissionInSystem::RFR_LIST]);

        $motTestNumber = 1;
        $categoryId = 304;

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);

        $testMotTestData = $this->getMotTestDataClass4();

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => $defectId,
            'type' => $type,
        ];

        $validDefectData = [
            'rfrId' => $defectId,
            'type' => $type,
            'locationLateral' => $locationLateral,
            'locationLongitudinal' => $locationLongitudinal,
            'locationVertical' => $locationVertical,
            'comment' => $additionalInformation,
            'failureDangerous' => $failureDangerous,
        ];

        $this->getResultForAction2('post', 'add', $routeParams, null, $validDefectData);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @return array
     */
    public function testValidDefectDataProvider()
    {
        return [
            [
                'rfrId' => 1,
                'type' => 'prs',
                'locationLateral' => null,
                'locationLongitudinal' => null,
                'locationVertical' => null,
                'comment' => null,
                'failureDangerous' => false,
            ],
            [
                'rfrId' => 2,
                'type' => 'failure',
                'locationLateral' => 'nearside',
                'locationLongitudinal' => 'front',
                'locationVertical' => 'inner',
                'comment' => 'This is a comment',
                'failureDangerous' => false,
            ],
            [
                'rfrId' => 3,
                'type' => 'advisory',
                'locationLateral' => 'central',
                'locationLongitudinal' => 'rear',
                'locationVertical' => null,
                'comment' => null,
                'failureDangerous' => false,
            ],
        ];
    }

    /**
     * @dataProvider testBreadcrumbsDataProvider
     *
     * @param $breadcrumbKey
     * @param $breadcrumbValue
     * @param $motTestTypeCode
     */
    public function testCorrectBreadcrumbsAreDisplayed($breadcrumbKey, $breadcrumbValue, $motTestTypeCode)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService([PermissionInSystem::RFR_LIST]);

        $motTestNumber = 1;
        $categoryId = 304;
        $defectId = 502;
        $type = 'advisory';

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);


        switch ($motTestTypeCode) {
            case MotTestTypeCode::NORMAL_TEST:
                $testMotTestData = $this->getMotTestDataClass4Type(MotTestTypeCode::NORMAL_TEST);
                break;
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                $testMotTestData = $this->getMotTestDataClass4Type(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
                break;
            case MotTestTypeCode::TARGETED_REINSPECTION:
                $testMotTestData = $this->getMotTestDataClass4Type(MotTestTypeCode::TARGETED_REINSPECTION);
                break;
            default:
                $testMotTestData = $this->getMotTestDataClass4();
        }

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => $defectId,
            'type' => $type,
        ];

        $this->getResultForAction('add', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);

        /** @var ViewModel $layoutViewModel */
        $layoutViewModel = $this->controller->getPluginManager()->get('layout')->__invoke();
        $breadcrumbs = $layoutViewModel->getVariable('breadcrumbs');
        $this->assertArrayHasKey('breadcrumbs', $breadcrumbs);
        $breadcrumbs = $breadcrumbs['breadcrumbs'];

        $this->assertArrayHasKey($breadcrumbKey, $breadcrumbs);
        $this->assertEquals($breadcrumbValue, $breadcrumbs[$breadcrumbKey]);
    }

    /**
     * @return array
     */
    public function testBreadcrumbsDataProvider()
    {
        return [
            [
                'MOT test results',
                '/mot-test/1',
                MotTestTypeCode::NORMAL_TEST,
            ],
            [
                'Training test',
                '/mot-test/1',
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
            ],
            [
                'MOT test reinspection',
                '/mot-test/1',
                MotTestTypeCode::TARGETED_REINSPECTION,
            ],
        ];
    }

    /**
     * @return DefectDto
     */
    private function getDefect()
    {
        $testDefect = new DefectDto();
        $testDefect->setDescription('Defect description');

        return $testDefect;
    }

    /**
     * @return MotTest
     */
    private function getMotTestDataClass4Type($testType)
    {
        $testDataJSON = "{
  \"id\" : 1,
  \"brakeTestResult\" : {
    \"id\" : 999888003,
    \"generalPass\" : false,
    \"isLatest\" : true,
    \"commercialVehicle\" : true,
    \"numberOfAxles\" : 2,
    \"parkingBrakeEfficiency\" : 30,
    \"parkingBrakeEfficiencyPass\" : false,
    \"parkingBrakeEffortNearside\" : 31,
    \"parkingBrakeEffortOffside\" : 32,
    \"parkingBrakeEffortSecondaryNearside\" : 33,
    \"parkingBrakeEffortSecondaryOffside\" : 34,
    \"parkingBrakeEffortSingle\" : 35,
    \"parkingBrakeImbalance\" : 36,
    \"parkingBrakeImbalancePass\" : true,
    \"parkingBrakeLockNearside\" : false,
    \"parkingBrakeLockOffside\" : true,
    \"parkingBrakeLockPercent\" : 37,
    \"parkingBrakeLockSecondaryNearside\" : true,
    \"parkingBrakeLockSecondaryOffside\" : false,
    \"parkingBrakeLockSingle\" : false,
    \"parkingBrakeNumberOfAxles\" : 1,
    \"parkingBrakeSecondaryImbalance\" : 38,
    \"parkingBrakeTestType\" : \"GRADT\",
    \"serviceBrake1Data\" : {
      \"id\" : 999888009,
      \"effortNearsideAxel1\" : 50,
      \"effortNearsideAxel2\" : 51,
      \"effortNearsideAxel3\" : 52,
      \"effortOffsideAxel1\" : 53,
      \"effortOffsideAxel2\" : 54,
      \"effortOffsideAxel3\" : 55,
      \"effortSingle\" : 56,
      \"imbalanceAxle1\" : 58,
      \"imbalanceAxle2\" : 59,
      \"imbalanceAxle3\" : 60,
      \"imbalancePass\" : true,
      \"lockNearsideAxle1\" : false,
      \"lockNearsideAxle2\" : true,
      \"lockNearsideAxle3\" : false,
      \"lockOffsideAxle1\" : true,
      \"lockOffsideAxle2\" : false,
      \"lockOffsideAxle3\" : true,
      \"lockPercent\" : 68,
      \"lockSingle\" : false
    },
    \"serviceBrake1Efficiency\" : 39,
    \"serviceBrake1EfficiencyPass\" : true,
    \"serviceBrake1TestType\" : \"PLATE\",
    \"serviceBrake2Data\" : {
      \"id\" : 999888009,
      \"effortNearsideAxel1\" : 50,
      \"effortNearsideAxel2\" : 51,
      \"effortNearsideAxel3\" : 52,
      \"effortOffsideAxel1\" : 53,
      \"effortOffsideAxel2\" : 54,
      \"effortOffsideAxel3\" : 55,
      \"effortSingle\" : 56,
      \"imbalanceAxle1\" : 58,
      \"imbalanceAxle2\" : 59,
      \"imbalanceAxle3\" : 60,
      \"imbalancePass\" : true,
      \"lockNearsideAxle1\" : false,
      \"lockNearsideAxle2\" : true,
      \"lockNearsideAxle3\" : false,
      \"lockOffsideAxle1\" : true,
      \"lockOffsideAxle2\" : false,
      \"lockOffsideAxle3\" : true,
      \"lockPercent\" : 68,
      \"lockSingle\" : false
    },
    \"serviceBrake2Efficiency\" : 40,
    \"serviceBrake2EfficiencyPass\" : true,
    \"serviceBrake2TestType\" : \"FLOOR\",
    \"serviceBrakeIsSingleLine\" : true,
    \"singleInFront\" : false,
    \"vehicleWeight\" : 5000,
    \"weightIsUnladen\" : true,
    \"weightType\" : \"VSI\"
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
  \"statusCode\" : \"ACTIVE\",
  \"testTypeCode\" : \"".$testType."\",
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
        return new MotTest(json_decode($testDataJSON));
    }


    /**
     * @return MotTest
     */
    private function getMotTestDataClass4()
    {
        $testDataJSON = "{
  \"id\" : 1,
  \"brakeTestResult\" : {
    \"id\" : 999888003,
    \"generalPass\" : false,
    \"isLatest\" : true,
    \"commercialVehicle\" : true,
    \"numberOfAxles\" : 2,
    \"parkingBrakeEfficiency\" : 30,
    \"parkingBrakeEfficiencyPass\" : false,
    \"parkingBrakeEffortNearside\" : 31,
    \"parkingBrakeEffortOffside\" : 32,
    \"parkingBrakeEffortSecondaryNearside\" : 33,
    \"parkingBrakeEffortSecondaryOffside\" : 34,
    \"parkingBrakeEffortSingle\" : 35,
    \"parkingBrakeImbalance\" : 36,
    \"parkingBrakeImbalancePass\" : true,
    \"parkingBrakeLockNearside\" : false,
    \"parkingBrakeLockOffside\" : true,
    \"parkingBrakeLockPercent\" : 37,
    \"parkingBrakeLockSecondaryNearside\" : true,
    \"parkingBrakeLockSecondaryOffside\" : false,
    \"parkingBrakeLockSingle\" : false,
    \"parkingBrakeNumberOfAxles\" : 1,
    \"parkingBrakeSecondaryImbalance\" : 38,
    \"parkingBrakeTestType\" : \"GRADT\",
    \"serviceBrake1Data\" : {
      \"id\" : 999888009,
      \"effortNearsideAxel1\" : 50,
      \"effortNearsideAxel2\" : 51,
      \"effortNearsideAxel3\" : 52,
      \"effortOffsideAxel1\" : 53,
      \"effortOffsideAxel2\" : 54,
      \"effortOffsideAxel3\" : 55,
      \"effortSingle\" : 56,
      \"imbalanceAxle1\" : 58,
      \"imbalanceAxle2\" : 59,
      \"imbalanceAxle3\" : 60,
      \"imbalancePass\" : true,
      \"lockNearsideAxle1\" : false,
      \"lockNearsideAxle2\" : true,
      \"lockNearsideAxle3\" : false,
      \"lockOffsideAxle1\" : true,
      \"lockOffsideAxle2\" : false,
      \"lockOffsideAxle3\" : true,
      \"lockPercent\" : 68,
      \"lockSingle\" : false
    },
    \"serviceBrake1Efficiency\" : 39,
    \"serviceBrake1EfficiencyPass\" : true,
    \"serviceBrake1TestType\" : \"PLATE\",
    \"serviceBrake2Data\" : {
      \"id\" : 999888009,
      \"effortNearsideAxel1\" : 50,
      \"effortNearsideAxel2\" : 51,
      \"effortNearsideAxel3\" : 52,
      \"effortOffsideAxel1\" : 53,
      \"effortOffsideAxel2\" : 54,
      \"effortOffsideAxel3\" : 55,
      \"effortSingle\" : 56,
      \"imbalanceAxle1\" : 58,
      \"imbalanceAxle2\" : 59,
      \"imbalanceAxle3\" : 60,
      \"imbalancePass\" : true,
      \"lockNearsideAxle1\" : false,
      \"lockNearsideAxle2\" : true,
      \"lockNearsideAxle3\" : false,
      \"lockOffsideAxle1\" : true,
      \"lockOffsideAxle2\" : false,
      \"lockOffsideAxle3\" : true,
      \"lockPercent\" : 68,
      \"lockSingle\" : false
    },
    \"serviceBrake2Efficiency\" : 40,
    \"serviceBrake2EfficiencyPass\" : true,
    \"serviceBrake2TestType\" : \"FLOOR\",
    \"serviceBrakeIsSingleLine\" : true,
    \"singleInFront\" : false,
    \"vehicleWeight\" : 5000,
    \"weightIsUnladen\" : true,
    \"weightType\" : \"VSI\"
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
  \"statusCode\" : \"ACTIVE\",
  \"testTypeCode\" : \"ADVISORY\",
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
        return new MotTest(json_decode($testDataJSON));
    }
}
