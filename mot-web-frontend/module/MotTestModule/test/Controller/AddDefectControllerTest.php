<?php

namespace DvsaMotTestTest\Controller;

use Application\Service\LoggedInUserManager;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\AddDefectController;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Model\ViewModel;

/**
 * Class AddDefectControllerTest.
 */
class AddDefectControllerTest extends AbstractFrontendControllerTestCase
{
    private $loggedInUserManagerMock;

    protected function setUp()
    {
        Bootstrap::setupServiceManager();
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->setController(new AddDefectController());
        $this->getController()->setServiceLocator($this->serviceManager);

        $this->loggedInUserManagerMock = XMock::of(
            LoggedInUserManager::class,
            []
        );

        $this->serviceManager->setService('LoggedInUserManager', $this->loggedInUserManagerMock);

        parent::setUp();
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

        $vehicleClassMock = $this->getMockBuilder(VehicleClassDto::class)->getMock();
        $vehicleClassMock->expects($this->any())
            ->method('getCode')
            ->willReturn(VehicleClassCode::CLASS_1);

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);

        $motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);

        $restClientMock
            ->expects($this->at(1))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber)->toString())
            ->willReturn(['data' => $motTestMock]);

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

        $vehicleClassMock = $this->getMockBuilder(VehicleClassDto::class)->getMock();
        $vehicleClassMock->expects($this->any())
            ->method('getCode')
            ->willReturn(VehicleClassCode::CLASS_1);

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);

        $motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);

        $restClientMock
            ->expects($this->at(1))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber)->toString())
            ->willReturn(['data' => $motTestMock]);

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

        $vehicleClassMock = $this->getMockBuilder(VehicleClassDto::class)->getMock();
        $vehicleClassMock->expects($this->any())
            ->method('getCode')
            ->willReturn(VehicleClassCode::CLASS_1);

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($motTestTypeCode);

        $motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId)->toString())
            ->willReturn(['data' => $this->getDefect()]);

        $restClientMock
            ->expects($this->at(1))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber)->toString())
            ->willReturn(['data' => $motTestMock]);

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
}
