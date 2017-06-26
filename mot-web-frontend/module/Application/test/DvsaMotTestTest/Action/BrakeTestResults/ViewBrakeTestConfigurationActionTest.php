<?php

namespace DvsaMotTestTest\Action\BrakeTestResults;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Dto\BrakeTest\BrakeTestTypeDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Model\BrakeTestConfigurationClass3AndAboveHelper;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class ViewBrakeTestConfigurationActionTest extends TestCase
{
    const FORM_VALIDATION_ERROR = 'Error: Form validation error';

    /**
     * @var stdClass
     */
    private $motTestData;

    /**
     * @var DvsaVehicle|MockObject
     */
    private $mockDvsaVehicle;

    /**
     * @var VehicleService|MockObject
     */
    private $mockVehicleService;

    /**
     * @var MotTestService|MockObject
     */
    private $mockMotTestService;

    /**
     * @var CatalogService|MockObject
     */
    private $mockCatalogService;

    /**
     * @var Client
     */
    private $mockRestClient;

    public function setUp()
    {
        $this->motTestData = new stdClass();
        $this->motTestData->motTestNumber = 295116285800;
        $this->motTestData->vehicleId = 1;
        $this->motTestData->vehicleVersion = 1;
        $this->motTestData->status = MotTestStatusName::ACTIVE;
        $this->motTestData->vehicleClass = new stdClass();
        $this->motTestData->vehicleWeight = 10000;
        $this->motTestData->testTypeCode = MotTestTypeCode::NORMAL_TEST;
        $this->motTestData->previousTestVehicleWeight = 9999;
        $this->motTestData->brakeTestResult = new stdClass();
        $this->motTestData->brakeTestResult->brakeTestTypeCode = 1;
        $this->motTestData->brakeTestResult->vehicleWeightFront = 600;
        $this->motTestData->brakeTestResult->vehicleWeightRear = 600;
        $this->motTestData->brakeTestResult->riderWeight = 100;
        $this->motTestData->brakeTestResult->isSidecarAttached = false;
        $this->motTestData->brakeTestResult->sidecarWeight = 300;
        $this->motTestData->brakeTestResult->vehicleWeight = 1;
        $this->motTestData->brakeTestResult->serviceBrakeIsSingleLine = false;
        $this->motTestData->brakeTestResult->numberOfAxles = 4;
        $this->motTestData->brakeTestResult->parkingBrakeNumberOfAxles = 2;
        $this->motTestData->brakeTestResult->weightType = '';
        $this->motTestData->brakeTestResult->serviceBrake1TestType = '';
        $this->motTestData->brakeTestResult->serviceBrake2TestType = '';
        $this->motTestData->brakeTestResult->parkingBrakeTestType = '';
        $this->motTestData->brakeTestResult->weightIsUnladen = false;
        $this->motTestData->brakeTestResult->commercialVehicle = false;
        $this->motTestData->brakeTestResult->singleInFront = false;

        $this->mockDvsaVehicle = XMock::of(DvsaVehicle::class);
        $this->mockMotTestService = XMock::of(MotTestService::class);
        $this->mockVehicleService = XMock::of(VehicleService::class);
        $this->mockCatalogService = XMock::of(CatalogService::class);
        $this->mockRestClient = XMock::of(Client::class);
    }

    public function testRedirectWithErrorMessageWhenMotTestIsNotActive()
    {
        $this->withMotTestStatus(MotTestStatusName::PASSED);

        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(1);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertEquals(MotTestController::ROUTE_MOT_TEST, $actionResult->getRouteName());
        $this->assertContains(
            InvalidTestStatus::ERROR_MESSAGE_TEST_COMPLETE,
            $actionResult->getErrorMessages()
        );
    }

    /**
     * @dataProvider dataProviderTestCorrectViewIsDisplayed
     *
     * @param string $vehicleClassCode
     * @param string $template
     */
    public function testCorrectViewIsDisplayed($vehicleClassCode, $template)
    {
        $this->mockMethods($vehicleClassCode);

        $action = $this->buildAction();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $action->execute(1);

        $this->assertEquals($template, $actionResult->getTemplate());
    }

    /**
     * @return array
     */
    public function dataProviderTestCorrectViewIsDisplayed()
    {
        return [
            ['1', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2],
            ['2', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_1_2],
            ['3', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE],
            ['4', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE],
            ['5', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE],
            ['7', ViewBrakeTestConfigurationAction::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE],
        ];
    }

    public function testGroupBDefaultValuesAreSet()
    {
        $this->mockMethods();

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeLineType(),
            BrakeTestConfigurationClass3AndAboveHelper::BRAKE_LINE_TYPE_DUAL);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getNumberOfAxles(),
            $this->motTestData->brakeTestResult->numberOfAxles);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeNumberOfAxles(),
            $this->motTestData->brakeTestResult->parkingBrakeNumberOfAxles);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getWeightType(),
            $this->motTestData->brakeTestResult->weightType);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeTestType(),
            $this->motTestData->brakeTestResult->serviceBrake1TestType);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeTestType(),
            $this->motTestData->brakeTestResult->parkingBrakeTestType);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getVehicleWeight(),
            $this->motTestData->brakeTestResult->vehicleWeight);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getWeightIsUnladen(),
            $this->motTestData->brakeTestResult->weightIsUnladen);
        $this->assertEquals($actionResult->getViewModel()->configHelper->getVehiclePurposeType(),
            BrakeTestConfigurationClass3AndAboveHelper::PURPOSE_PERSONAL);
        $this->assertEquals($actionResult->getViewModel()->configHelper->isSingleWheelInFront(),
            $this->motTestData->brakeTestResult->singleInFront);
    }

    public function testDtoPopulatedAndErrorMessagesDisplayFromPreviousAction()
    {
        $this->mockMethods();

        $action = $this->buildAction()->setPreviousActionResult(
            (new ViewActionResult())->addErrorMessage(self::FORM_VALIDATION_ERROR),
            [
                'serviceBrake1TestType' => 'ROLLR',
                'parkingBrakeTestType' => 'ROLLR',
                'vehicleWeight' => '',
                'brakeLineType' => 'dual',
                'numberOfAxles' => '2',
                'parkingBrakeNumberOfAxles' => '1',
                'vehicleClass' => '4',
            ]
        );

        $actionResult = $action->execute(1);

        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeTestType(),
        'ROLLR');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeTestType(),
        'ROLLR');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getVehicleWeight(),
            '');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeLineType(),
            'dual');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getNumberOfAxles(),
            '2');
        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeNumberOfAxles(),
            '1');
        $this->assertEquals($actionResult->getViewModel()->configHelper->locksApplicableToFirstServiceBrake(),
            true);
        $this->assertContains(
            self::FORM_VALIDATION_ERROR,
            $actionResult->getErrorMessages()
        );
    }

    /**
     * @dataProvider dataProviderTestPreselectedTestWeight
     *
     * @param int    $vehicleWeight
     * @param int    $previousTestVehicleWeight
     * @param string $serviceBrake1TestType
     * @param string $parkingBrakeTestType
     * @param bool   $expected
     */
    public function testPreselectedTestWeightForGroupBVehicle(
        $vehicleWeight, $previousTestVehicleWeight, $serviceBrake1TestType, $parkingBrakeTestType, $expected
    ) {
        $this->mockMethods();

        $this->withBrakeTestResults($vehicleWeight, $previousTestVehicleWeight, $serviceBrake1TestType, $parkingBrakeTestType);

        $action = $this->buildAction();

        $actionResult = $action->execute(1);

        $this->assertEquals($expected, $actionResult->getViewModel()->getVariables()['preselectBrakeTestWeight']);
    }

    /**
     * @return array
     */
    public function dataProviderTestPreselectedTestWeight()
    {
        return [
            [10000, 9999, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [10000, 9999, BrakeTestTypeCode::PLATE, BrakeTestTypeCode::PLATE, true],
            [10000, 9999, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::DECELEROMETER, true],
            [10000, 9999, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
            [10000, null, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [null, 9999, BrakeTestTypeCode::PLATE, BrakeTestTypeCode::PLATE, true],
            [null, null, BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::ROLLER, true],
            [null, 9999, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
            [null, null, BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::DECELEROMETER, false],
        ];
    }

    public function testGroupBIfNoBrakeTestResultsThenPopulateBrakeTestTypesInDtoWithSiteDefaults()
    {
        $this->withoutBrakeTestResult();

        $this->withSite();

        $this->mockMethods();

        $this->mockRestClient
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue(
                [
                    'data' => (new VehicleTestingStationDto())
                            ->setDefaultServiceBrakeTestClass3AndAbove(
                                (new BrakeTestTypeDto())
                                    ->setName('DefaultServiceBrakeTestType')
                                    ->setCode('DefaultServiceBrakeTestTypeCode')
                            )
                            ->setDefaultParkingBrakeTestClass3AndAbove(
                                (new BrakeTestTypeDto())
                                    ->setName('DefaultParkingBrakeTestType')
                                    ->setCode('DefaultParkingBrakeTestTypeCode')
                            ),
                ]
            ));

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        $this->assertEquals($actionResult->getViewModel()->configHelper->getServiceBrakeTestType(),
            'DefaultServiceBrakeTestTypeCode');

        $this->assertEquals($actionResult->getViewModel()->configHelper->getParkingBrakeTestType(),
            'DefaultParkingBrakeTestTypeCode');
    }

    public function testGroupAIfNoBrakeTestResultsThenPopulateBrakeTestTypesInDtoWithMapperDefaults()
    {
        $this->withoutBrakeTestResult();

        $this->withSite();

        $vehicleClass = 1;
        $this->mockMethods($vehicleClass);

        $action = $this->buildAction();

        /** @var ViewActionResult $actionResult */
        $actionResult = $action->execute(1);

        // @see BrakeTestConfigurationClass1And2Mapper::mapToDefaultDto

        $this->assertEquals(BrakeTestTypeCode::ROLLER, $actionResult->getViewModel()->brakeTestType);
        $this->assertEquals('', $actionResult->getViewModel()->vehicleWeightFront);
        $this->assertEquals('', $actionResult->getViewModel()->vehicleWeightRear);
        $this->assertEquals('', $actionResult->getViewModel()->riderWeight);
        $this->assertEquals('', $actionResult->getViewModel()->sidecarWeight);
    }

    private function withoutBrakeTestResult()
    {
        $this->motTestData->brakeTestResult = null;

        return $this;
    }

    private function withSite()
    {
        $this->motTestData->site = new stdClass();
        $this->motTestData->site->id = 5;
        $this->motTestData->site->number = 555;
        $this->motTestData->site->name = 'Site 1';
        $this->motTestData->site->address = [];

        return $this->motTestData->site;
    }

    /**
     * @param string $vehicleClassCode
     */
    private function mockMethods($vehicleClassCode = '4')
    {
        $this->mockVehicleService
            ->expects($this->any())
            ->method('getDvsaVehicleByIdAndVersion')
            ->willReturn($this->mockDvsaVehicle);

        $this->mockDvsaVehicle
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn(
                (new VehicleClassDto())
                    ->setCode($vehicleClassCode)
            );

        $this->mockCatalogService
            ->method('getBrakeTestTypes')
            ->willReturn([]);
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    private function withMotTestStatus($status)
    {
        $this->motTestData->status = $status;

        return $this;
    }

    /**
     * @param int    $vehicleWeight
     * @param int    $previousTestVehicleWeight
     * @param string $serviceBrake1TestType
     * @param string $parkingBrakeTestType
     *
     * @return $this
     */
    private function withBrakeTestResults($vehicleWeight, $previousTestVehicleWeight, $serviceBrake1TestType, $parkingBrakeTestType)
    {
        $this->motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $this->motTestData->brakeTestResult->previousTestVehicleWeight = $previousTestVehicleWeight;
        $this->motTestData->brakeTestResult->serviceBrake1TestType = $serviceBrake1TestType;
        $this->motTestData->brakeTestResult->parkingBrakeTestType = $parkingBrakeTestType;

        return $this;
    }

    /**
     * @return ViewBrakeTestConfigurationAction
     */
    private function buildAction()
    {
        $this->mockMotTestService
            ->expects($this->any())
            ->method('getMotTestByTestNumber')
            ->willReturn(new MotTest($this->motTestData));

        $action = new ViewBrakeTestConfigurationAction(
            XMock::of(WebPerformMotTestAssertion::class),
            XMock::of(ContingencySessionManager::class),
            $this->mockCatalogService,
            $this->mockRestClient,
            XMock::of(BrakeTestConfigurationContainerHelper::class),
            $this->mockVehicleService,
            $this->mockMotTestService,
            XMock::of(BrakeTestConfigurationService::class)
        );

        return $action;
    }
}
