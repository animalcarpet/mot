<?php

namespace DvsaMotTestTest\Controller;

use Application\Service\LoggedInUserManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\EditDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTestTest\TestHelper\Fixture;

/**
 * Class EditDefectControllerTest.
 */
class EditDefectControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var LoggedInUserManager | \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggedInUserManagerMock;

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
        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $this->setServiceManager($this->serviceManager);

        $this->defectsJourneyContextProviderMock = $this
            ->getMockBuilder(DefectsJourneyContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->defectsJourneyUrlGeneratorMock = $this
            ->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setController(new EditDefectController(
            $this->defectsJourneyContextProviderMock, $this->defectsJourneyUrlGeneratorMock));
        $this->getController()->setServiceLocator($this->serviceManager);

        $this->loggedInUserManagerMock = $this
            ->getMockBuilder(LoggedInUserManager::class)
            ->disableOriginalConstructor()
            ->getMock();

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
     * Test that the Edit Defect page loads correctly.
     */
    public function testEditActionWithGetMethod()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService([PermissionInSystem::RFR_LIST]);

        $motTestNumber = 1;
        $defectId = 1;

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'identifiedDefectId' => $defectId,
        ];

        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);

        $rfrs = $this->getFailuresPrsAndAdvisories(1, 1, 2);
        $testMotTestData->reasonsForRejection = $rfrs;

        $motTest = new MotTest($testMotTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $this->getResultForAction2('get', 'edit', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test that you can edit a defect.
     */
    public function testEditActionWithPostMethod()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());
        $this->setupAuthorizationService([PermissionInSystem::RFR_LIST]);

        $motTestNumber = 1;
        $defectId = 1;

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'identifiedDefectId' => $defectId,
        ];

        $postParams = [
            'id' => $defectId,
            'locationLateral' => 'n/a',
            'locationLongitudinal' => 'n/a',
            'locationVertical' => 'n/a',
            'comment' => 'This is broken',
            'failureDangerous' => true,
        ];

        $this->defectsJourneyUrlGeneratorMock->method('goBack')->willReturn('mot-test');

        $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
        $rfrs = $this->getFailuresPrsAndAdvisories(1, 1, 2);
        $testMotTestData->reasonsForRejection = $rfrs;

        $motTest = new MotTest($testMotTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $this->getResultForAction2('post', 'edit', $routeParams, [], $postParams);
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    /**
     * @param int $failures
     * @param int $prs
     * @param int $advisories
     *
     * @return array[] reasonsForRejection
     */
    private function getFailuresPrsAndAdvisories($failures, $prs, $advisories)
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

        $this->reasonsForRejection = [
            'FAIL' => $failArray,
            'PRS' => $prsArray,
            'ADVISORY' => $advisoryArray,
        ];

        $this->reasonsForRejection = json_decode(json_encode($this->reasonsForRejection), FALSE);
        return $this->reasonsForRejection;
    }
}