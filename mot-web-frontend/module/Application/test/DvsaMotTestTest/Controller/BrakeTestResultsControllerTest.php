<?php

namespace DvsaMotTestTest\Controller;

use Core\Action\AbstractActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Controller\BrakeTestResultsController;
use DvsaMotTest\Controller\MotTestController;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Model\BrakeTestResultClass1And2ViewModel;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class BrakeTestResultsControllerTest.
 */
class BrakeTestResultsControllerTest extends AbstractDvsaMotTestTestCase
{
    protected $brakeTestConfigurationContainerMock;
    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    /** @var SubmitBrakeTestConfigurationAction|MockObject $submitBrakeTestConfigurationAction */
    private $submitBrakeTestConfigurationAction;

    /** @var ViewBrakeTestConfigurationAction|MockObject $viewBrakeTestConfigurationAction */
    private $viewBrakeTestConfigurationAction;

    protected function setUp()
    {
        $this->submitBrakeTestConfigurationAction = XMock::of(SubmitBrakeTestConfigurationAction::class);
        $this->viewBrakeTestConfigurationAction = XMock::of(ViewBrakeTestConfigurationAction::class);

        $this->controller = new BrakeTestResultsController(
            $this->submitBrakeTestConfigurationAction,
            $this->viewBrakeTestConfigurationAction
        );

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $webPerformMotTestAssertion = XMock::of(WebPerformMotTestAssertion::class);
        $serviceManager->setService(WebPerformMotTestAssertion::class, $webPerformMotTestAssertion);
        $this->brakeTestConfigurationContainerMock = XMock::of(BrakeTestConfigurationContainerHelper::class);
        $serviceManager->setService(
            'BrakeTestConfigurationContainerHelper',
            $this->brakeTestConfigurationContainerMock
        );

        $serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );

        $this->controller->setServiceLocator($serviceManager);
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

    public function testBrakeTestResultsCanBeAccessedAuthenticatedRequest()
    {
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));

        $vehicleWeight = '1000';
        $queryData = [
            'vehicleWeight' => $vehicleWeight,
            'weightType' => 'vsi',
            'serviceBrake1TestType' => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType' => BrakeTestTypeCode::ROLLER,
            'brakeLineType' => 'dual',
            'numberOfAxles' => '2',
            'parkingBrakeNumberOfAxles' => '1',
            'vehicleClass' => VehicleClassCode::CLASS_4,
        ];

        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->getResponseForAction('addBrakeTestResults', ['motTestNumber' => '1']);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testBrakeTestResultsPostWithValidData()
    {
        $motTestNumber = 1;
        $brakeTestValue = '76';
        $vehicleWeight = '1000';
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $queryData = [
            'vehicleWeight' => $vehicleWeight,
            'weightType' => 'vsi',
            'serviceBrake1TestType' => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType' => BrakeTestTypeCode::ROLLER,
            'brakeLineType' => 'dual',
            'numberOfAxles' => '2',
            'parkingBrakeNumberOfAxles' => '1',
            'vehicleClass' => VehicleClassCode::CLASS_4,
        ];

        $this->brakeTestConfigurationContainerMock
            ->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = [
            'serviceBrakeEffortNearsideAxle1' => $brakeTestValue,
            'serviceBrakeEffortOffsideAxle1' => $brakeTestValue,
            'serviceBrakeEffortNearsideAxle2' => $brakeTestValue,
            'serviceBrakeEffortOffsideAxle2' => $brakeTestValue,
            'parkingBrakeEffortNearside' => $brakeTestValue,
            'parkingBrakeEffortOffside' => $brakeTestValue,
            'parkingBrakeLockOffside' => '1',
            'parkingBrakeLockNearside' => '0',
            'vehicleClass' => VehicleClassCode::CLASS_4,
        ];

        $expectedRestPostData = [
            'vehicleWeight' => $vehicleWeight,
            'weightType' => 'vsi',
            'serviceBrake1TestType' => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType' => BrakeTestTypeCode::ROLLER,
            'numberOfAxles' => 2,
            'parkingBrakeNumberOfAxles' => 1,
            'parkingBrakeEffortNearside' => $brakeTestValue,
            'parkingBrakeEffortOffside' => $brakeTestValue,
            'parkingBrakeLockNearside' => null,
            'parkingBrakeLockOffside' => true,
            'serviceBrakeIsSingleLine' => false,
            'weightIsUnladen' => false,
            'isCommercialVehicle' => false,
            'serviceBrake1Data' => [
                'effortNearsideAxle1' => $brakeTestValue,
                'effortOffsideAxle1' => $brakeTestValue,
                'effortNearsideAxle2' => $brakeTestValue,
                'effortOffsideAxle2' => $brakeTestValue,
                'lockNearsideAxle1' => false,
                'lockOffsideAxle1' => false,
                'lockNearsideAxle2' => false,
                'lockOffsideAxle2' => false,
            ],
            'serviceBrakeControlsCount' => 0,
            'parkingBrakeEffortSingle' => null,
            'parkingBrakeLockSingle' => null,
            'isParkingBrakeOnTwoWheels' => true,
            'isSingleInFront' => null,
            'serviceBrake2TestType' => null,
            '_class' => BrakeTestConfigurationClass3AndAboveDto::class,
            'vehicleClass' => VehicleClassCode::CLASS_4,
        ];

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->setPostAndPostParams($postData);

        $result = $this->getResultForAction('addBrakeTestResults', ['motTestNumber' => $motTestNumber]);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    public function testBrakeTestResultsPostClass12()
    {
        $motTestNumber = 1;
        $brakeTestValue = 80;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $queryData = [
            'vehicleWeightFront' => $brakeTestValue,
            'vehicleWeightRear' => $brakeTestValue,
            'riderWeight' => $brakeTestValue,
            'isSidecarAttached' => '0',
            'brakeTestType' => BrakeTestTypeCode::ROLLER,
        ];

        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = [
            'control1EffortFront' => $brakeTestValue,
            'control1EffortRear' => $brakeTestValue,
            'control2EffortFront' => $brakeTestValue,
            'control2EffortRear' => $brakeTestValue,
            'control1LockFront' => 1,
            'control1LockRear' => 0,
            'vehicleClass' => VehicleClassCode::CLASS_1,
        ];
        $this->setPostAndPostParams($postData);

        $expectedRestPostData = array_merge(
            [
                'control1EffortFront' => $brakeTestValue,
                'control1EffortRear' => $brakeTestValue,
                'control2EffortFront' => $brakeTestValue,
                'control2EffortRear' => $brakeTestValue,
                'vehicleWeightFront' => $brakeTestValue,
                'vehicleWeightRear' => $brakeTestValue,
                'riderWeight' => $brakeTestValue,
                'isSidecarAttached' => false,
                'brakeTestType' => BrakeTestTypeCode::ROLLER,
                'control1LockFront' => true,
                'control1LockRear' => false,
                'control1EffortSidecar' => null,
                'control2LockFront' => false,
                'control2LockRear' => false,
                'control2EffortSidecar' => null,
                'sidecarWeight' => null,
                '_class' => BrakeTestConfigurationClass1And2Dto::class,
            ]
        );

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->getResultForAction('addBrakeTestResults', ['motTestNumber' => $motTestNumber]);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    /**
     * @param $vehicleClass
     *
     * @dataProvider provideVehicleClasses
     */
    public function testBrakeTestResultsPostWithInvalidData($vehicleClass)
    {
        $motTestNumber = 1;
        $errorMessage = "Value is required and can't be empty";
        $this->routeMatch->setParam('action', 'addBrakeTestResults');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);
        $this->request->getPost()->set('vehicleClass', $vehicleClass);
        $this->request->setMethod('post');

        if ($vehicleClass < 3) {
            $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));

            $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
            $mockMotTestServiceClient
                ->expects($this->once())
                ->method('getMotTestByTestNumber')
                ->with(1)
                ->will($this->returnValue($testMotTestData));

            $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));

            $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
            $mockVehicleServiceClient
                ->expects($this->once())
                ->method('getDvsaVehicleByIdAndVersion')
                ->with(1001, 1)
                ->will($this->returnValue($vehicleData));
        } else {
            $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

            $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
            $mockMotTestServiceClient
                ->expects($this->once())
                ->method('getMotTestByTestNumber')
                ->with(1)
                ->will($this->returnValue($testMotTestData));

            $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

            $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
            $mockVehicleServiceClient
                ->expects($this->once())
                ->method('getDvsaVehicleByIdAndVersion')
                ->with(1001, 1)
                ->will($this->returnValue($vehicleData));
        }

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->at(0))->method('save')
            ->will($this->throwException(new RestApplicationException('', '', [], 0)));

        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn([]);

        $this->getFlashMessengerMockForAddManyErrorMessage($errorMessage);

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function provideVehicleClasses()
    {
        return [
            [VehicleClassCode::CLASS_1],
            [VehicleClassCode::CLASS_2],
            [VehicleClassCode::CLASS_3],
            [VehicleClassCode::CLASS_4],
            [VehicleClassCode::CLASS_5],
            [VehicleClassCode::CLASS_7],
        ];
    }

    public function testBrakeTestSummaryCanBeAccessedAuthenticatedRequest()
    {
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $response = $this->getResponseForAction('displayBrakeTestSummary', ['motTestNumber' => '1']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public static function gradientTestsForClass12()
    {
        return [
            [
                [
                    'input' => [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass' => VehicleClassCode::CLASS_1,
                        ],
                    'output' => [
                            'gradientControl1AboveUpperMinimum' => true,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl1BelowMinimum' => false,
                            'gradientControl2BelowMinimum' => false,
                        ],
                ],
            ],
            [
                [
                    'input' => [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass' => VehicleClassCode::CLASS_1,
                        ],
                    'output' => [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum' => false,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl2BelowMinimum' => false,
                        ],
                ],
            ],
            [
                [
                    'input' => [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass' => VehicleClassCode::CLASS_1,
                        ],
                    'output' => [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum' => true,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl2BelowMinimum' => false,
                        ],
                ],
            ],
            [
                [
                    'input' => [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'vehicleClass' => VehicleClassCode::CLASS_1,
                        ],
                    'output' => [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum' => true,
                            'gradientControl2AboveUpperMinimum' => false,
                            'gradientControl2BelowMinimum' => true,
                        ],
                ],
            ],
            [
                [
                    'input' => [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'vehicleClass' => VehicleClassCode::CLASS_1,
                        ],
                    'output' => [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum' => false,
                            'gradientControl2AboveUpperMinimum' => false,
                            'gradientControl2BelowMinimum' => false,
                        ],
                ],
            ],
        ];
    }

    /**
     * @param $testCase
     *
     * @dataProvider gradientTestsForClass12
     */
    public function testGradientBrakeTestResultsPostClass12($testCase)
    {
        $motTestNumber = 1;
        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass1(true));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $this->routeMatch->setParam('action', 'addBrakeTestResults');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $queryData = [
            'isSidecarAttached' => '0',
            'brakeTestType' => BrakeTestTypeCode::GRADIENT,
        ];
        $this->brakeTestConfigurationContainerMock
            ->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = $testCase['input'];
        $this->setPostAndPostParams($postData);

        $expectedRestPostData = array_replace_recursive(
            [
                'isSidecarAttached' => false,
                'brakeTestType' => BrakeTestTypeCode::GRADIENT,
                'sidecarWeight' => null,
                'control1EffortSidecar' => null,
                'control2EffortSidecar' => null,
                'control2EffortFront' => null,
                'control2EffortRear' => null,
                'vehicleWeightFront' => null,
                'vehicleWeightRear' => null,
                'riderWeight' => null,
                'control1EffortFront' => null,
                'control1EffortRear' => null,
                '_class' => BrakeTestConfigurationClass1And2Dto::class,
            ],
            $testCase['output']
        );

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->controller->dispatch($this->request);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    public function testBrakeTestConfigRedirectsWhenSubmitActionReturnsRedirectResult()
    {
        $motTestNumber = 8;
        $redirectActionResult = new RedirectToRoute(MotTestController::ROUTE_MOT_TEST, ['motTestNumber' => $motTestNumber]);
        $this->withSubmitBrakeTestConfigurationResult($redirectActionResult);

        $this->routeMatch->setParam('action', 'configureBrakeTest');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $this->setPostAndPostParams([]);
        $this->controller->dispatch($this->request);

        $response = $this->getController()->getResponse();
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE, $response);
    }

    public function testBrakeTestConfigSuccessWhenSubmitActionReturnsViewResult()
    {
        $motTestNumber = 8;
        $viewActionResult = new ViewActionResult();
        $this->withViewBrakeTestConfigurationResult($viewActionResult);

        $this->routeMatch->setParam('action', 'configureBrakeTest');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $this->setPostAndPostParams([]);
        $this->controller->dispatch($this->request);

        $response = $this->getController()->getResponse();
        $this->assertResponseStatus(self::HTTP_OK_CODE, $response);
    }

    protected function getRestClientMockWithGetMotTest($motTestData, $minimal = false)
    {
        $motTestNumber = is_object($motTestData['data']) ? $motTestData['data']->getMotTestNumber()
            : $motTestData['data']['motTestNumber'];
        $restClientMock = $this->getRestClientMockForServiceManager();
        $endpoint = $minimal ? "mot-test/$motTestNumber/minimal" : "mot-test/$motTestNumber";
        $restClientMock
            ->method('get')
            ->with($endpoint)
            ->will($this->returnValue($motTestData));

        return $restClientMock;
    }

    private function getBrakeTestResultsResourcesMock()
    {
        $mock = XMock::of(BrakeTestResultsResource::class);

        $serviceManager = $this->getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(BrakeTestResultsResource::class, $mock);

        return $mock;
    }

    private function withSubmitBrakeTestConfigurationResult(AbstractActionResult $result)
    {
        $this->submitBrakeTestConfigurationAction
            ->expects($this->any())
            ->method('execute')
            ->willReturn($result);

        return $this;
    }

    private function withViewBrakeTestConfigurationResult(AbstractActionResult $result)
    {
        $this->viewBrakeTestConfigurationAction
            ->expects($this->any())
            ->method('execute')
            ->willReturn($result);

        return $this;
    }
}
