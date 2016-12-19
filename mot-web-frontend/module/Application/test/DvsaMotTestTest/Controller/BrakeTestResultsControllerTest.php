<?php
namespace DvsaMotTestTest\Controller;

use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\BrakeTestResultsController;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Model\BrakeTestResultClass1And2ViewModel;

/**
 * Class BrakeTestResultsControllerTest
 */
class BrakeTestResultsControllerTest extends AbstractDvsaMotTestTestCase
{
    protected $brakeTestConfigurationContainerMock;

    protected function setUp()
    {
        $this->controller = new BrakeTestResultsController();

        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $webPerformMotTestAssertion = XMock::of(WebPerformMotTestAssertion::class);
        $serviceManager->setService(WebPerformMotTestAssertion::class, $webPerformMotTestAssertion);
        $this->brakeTestConfigurationContainerMock = XMock::of(BrakeTestConfigurationContainerHelper::class);
        $serviceManager->setService(
            'BrakeTestConfigurationContainerHelper',
            $this->brakeTestConfigurationContainerMock
        );

        $this->controller->setServiceLocator($serviceManager);
        parent::setUp();
    }

    /**
     * @dataProvider brakeTestConfigurationTestItems
     */
    public function testBrakeTestConfigurationGetOk(
        $motTestNumber,
        $vehicleClass,
        $isPost,
        $postParams,
        $expectedTemplate,
        $expectedLocation = null
    ) {
        $motTestData = $this->getMotTestDataDto($vehicleClass, $motTestNumber);

        $this->getRestClientMockWithGetMotTest($motTestData);

        if ($isPost) {
            $this->setPostAndPostParams($postParams);
        }

        $result = $this->getResultForAction('configureBrakeTest', ['motTestNumber' => $motTestNumber]);

        if ($isPost) {
            $this->assertRedirectLocation2($expectedLocation);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
            $this->assertEquals($expectedTemplate, $result->getTemplate());
        }
    }

    public static function brakeTestConfigurationTestItems()
    {
        $postParamsClass1 = [
            'brakeTestType'      => BrakeTestTypeCode::ROLLER,
            'vehicleWeightFront' => '100',
            'vehicleWeightRear'  => '50',
            'riderWeight'        => '80',
            'isSidecarAttached'  => '1',
            'sidecarWeight'      => '40',
        ];
        $postParamsClass4 = [
            'serviceBrake1TestType'     => BrakeTestTypeCode::ROLLER,
            'serviceBrake2TestType'     => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType'      => BrakeTestTypeCode::PLATE,
            'weightType'                => 'presented',
            'vehicleWeight'             => '1000',
            'brakeLineType'             => 'single',
            'numberOfAxles'             => '2',
            'parkingBrakeNumberOfAxles' => '0',
            'serviceBrakeIsSingleLine'  => true,
            'weightIsUnladen'           => '1',
            'isCommercialVehicle'       => false,
        ];

        return [
            [
                'motTestNumber'    => 1,
                'vehicleClass'     => VehicleClassCode::CLASS_4,
                'isPost'           => false,
                'postParams'       => null,
                'expectedTemplate' => BrakeTestResultsController::TEMPLATE_CONFIG_CLASS_3_AND_ABOVE,
            ],
            [
                'motTestNumber'    => 1,
                'vehicleClass'     => VehicleClassCode::CLASS_1,
                'isPost'           => false,
                'postParams'       => null,
                'expectedTemplate' => BrakeTestResultsController::TEMPLATE_CONFIG_CLASS_1_2,
            ],
            [
                'motTestNumber'    => 1,
                'vehicleClass'     => VehicleClassCode::CLASS_1,
                'isPost'           => true,
                'postParams'       => $postParamsClass1,
                'expectedTemplate' => null,
                'expectedLocation' => '/mot-test/1/brake-test-results'
            ],
            [
                'motTestNumber'    => 1,
                'vehicleClass'     => VehicleClassCode::CLASS_4,
                'isPost'           => true,
                'postParams'       => $postParamsClass4,
                'expectedTemplate' => null,
                'expectedLocation' => '/mot-test/1/brake-test-results'
            ],
        ];
    }

    public function testBrakeTestResultsCanBeAccessedAuthenticatedRequest()
    {
        $this->getResponseForAction('addBrakeTestResults', ['id' => '1']);

        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testBrakeTestResultsPostWithValidData()
    {
        $motTestNumber = 1;
        $vehicleClassCode = VehicleClassCode::CLASS_4;
        $brakeTestValue = '76';
        $vehicleWeight = '1000';
        $motTestData = $this->getMotTestDataDto($vehicleClassCode, $motTestNumber);

        $queryData = [
            'vehicleWeight'             => $vehicleWeight,
            'weightType'                => 'vsi',
            'serviceBrake1TestType'     => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType'      => BrakeTestTypeCode::ROLLER,
            'brakeLineType'             => 'dual',
            'numberOfAxles'             => '2',
            'parkingBrakeNumberOfAxles' => '1',
            'vehicleClass' => $vehicleClassCode
        ];

        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = [
            'serviceBrakeEffortNearsideAxle1' => $brakeTestValue,
            'serviceBrakeEffortOffsideAxle1'  => $brakeTestValue,
            'serviceBrakeEffortNearsideAxle2' => $brakeTestValue,
            'serviceBrakeEffortOffsideAxle2'  => $brakeTestValue,
            'parkingBrakeEffortNearside'      => $brakeTestValue,
            'parkingBrakeEffortOffside'       => $brakeTestValue,
            'parkingBrakeLockOffside'         => '1',
            'parkingBrakeLockNearside'        => '0',
            'vehicleClass' => VehicleClassCode::CLASS_4
        ];

        $this->routeMatch->setParam('action', 'addBrakeTestResults');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $this->setPostAndPostParams($postData);

        $expectedRestPostData = [
            'vehicleWeight'              => $vehicleWeight,
            'weightType'                 => 'vsi',
            'serviceBrake1TestType'      => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType'       => BrakeTestTypeCode::ROLLER,
            'numberOfAxles'              => 2,
            'parkingBrakeNumberOfAxles'  => 1,
            'parkingBrakeEffortNearside' => $brakeTestValue,
            'parkingBrakeEffortOffside'  => $brakeTestValue,
            'parkingBrakeLockNearside'   => null,
            'parkingBrakeLockOffside'    => true,
            'serviceBrakeIsSingleLine'   => false,
            'weightIsUnladen'            => false,
            'isCommercialVehicle'        => false,
            'serviceBrake1Data'          => [
                'effortNearsideAxle1' => $brakeTestValue,
                'effortOffsideAxle1'  => $brakeTestValue,
                'effortNearsideAxle2' => $brakeTestValue,
                'effortOffsideAxle2'  => $brakeTestValue,
                'lockNearsideAxle1'   => false,
                'lockOffsideAxle1'    => false,
                'lockNearsideAxle2'   => false,
                'lockOffsideAxle2'    => false,
            ],
            'serviceBrakeControlsCount'  => 0,
            'parkingBrakeEffortSingle'   => null,
            'parkingBrakeLockSingle'     => null,
            'isParkingBrakeOnTwoWheels'  => true,
            'isSingleInFront'            => null,
            'serviceBrake2TestType'      => null,
            '_class'                     => BrakeTestConfigurationClass3AndAboveDto::class,
            'vehicleClass'               => $vehicleClassCode
        ];

        $this->getRestClientMockWithGetMotTest($motTestData, true);

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->controller->dispatch($this->request);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    public function testBrakeTestResultsPostClass12()
    {
        $motTestNumber = 1;
        $brakeTestValue = 80;
        $motTestData = $this->getMotTestDataDto(VehicleClassCode::CLASS_1, $motTestNumber);

        $this->routeMatch->setParam('action', 'addBrakeTestResults');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $queryData = [
            'vehicleWeightFront' => $brakeTestValue,
            'vehicleWeightRear'  => $brakeTestValue,
            'riderWeight'        => $brakeTestValue,
            'isSidecarAttached'  => '0',
            'brakeTestType'      => BrakeTestTypeCode::ROLLER,
        ];
        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = [
            'control1EffortFront' => $brakeTestValue,
            'control1EffortRear'  => $brakeTestValue,
            'control2EffortFront' => $brakeTestValue,
            'control2EffortRear'  => $brakeTestValue,
            'control1LockFront'   => 1,
            'control1LockRear'    => 0,
            'vehicleClass'        => VehicleClassCode::CLASS_1
        ];
        $this->setPostAndPostParams($postData);
        unset($postData['vehicleClass']);

        $expectedRestPostData = array_merge(
            $postData,
            [
                'vehicleWeightFront'    => $brakeTestValue,
                'vehicleWeightRear'     => $brakeTestValue,
                'riderWeight'           => $brakeTestValue,
                'isSidecarAttached'     => false,
                'brakeTestType'         => BrakeTestTypeCode::ROLLER,
                'control1LockFront'     => true,
                'control1LockRear'      => false,
                'control1EffortSidecar' => null,
                'control2LockFront'     => false,
                'control2LockRear'      => false,
                'control2EffortSidecar' => null,
                'sidecarWeight'         => null,
                '_class'                => BrakeTestConfigurationClass1And2Dto::class,
            ]
        );

        $this->getRestClientMockWithGetMotTest($motTestData, true);

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->controller->dispatch($this->request);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    /**
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

        $this->getRestClientMockWithGetMotTest($this->getMotTestDataDto($vehicleClass));

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
        $this->getRestClientMockWithGetMotTest($this->getMotTestDataDto());
        $response = $this->getResponseForAction('displayBrakeTestSummary', ['motTestNumber' => '1']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_configureBrakeTestAction_redirects_back_when_test_is_not_in_progress()
    {
        $motTestNumber = 123;

        $motTest = $this->getMotTestDataDto(VehicleClassCode::CLASS_4, $motTestNumber, 1, 'PASSED');
        $this->getRestClientMockWithGetMotTest($motTest);

        $this->getResponseForAction('configureBrakeTest', ['motTestNumber' => $motTestNumber]);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber");
    }

    public static function gradientTestsForClass12()
    {
        return [
            [
                [
                    "input"  =>
                        [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass'     => VehicleClassCode::CLASS_1
                        ],
                    "output" =>
                        [
                            'gradientControl1AboveUpperMinimum' => true,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl1BelowMinimum'      => false,
                            'gradientControl2BelowMinimum'      => false,
                        ]
                ]
            ],
            [
                [
                    "input"  =>
                        [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass'     => VehicleClassCode::CLASS_1
                        ],
                    "output" =>
                        [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum'      => false,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl2BelowMinimum'      => false,
                        ]
                ]
            ],
            [
                [
                    "input"  =>
                        [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30,
                            'vehicleClass'     => VehicleClassCode::CLASS_1
                        ],
                    "output" =>
                        [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum'      => true,
                            'gradientControl2AboveUpperMinimum' => true,
                            'gradientControl2BelowMinimum'      => false,
                        ]
                ]
            ],
            [
                [
                    "input"  =>
                        [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25,
                            'vehicleClass'     => VehicleClassCode::CLASS_1
                        ],
                    "output" =>
                        [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum'      => true,
                            'gradientControl2AboveUpperMinimum' => false,
                            'gradientControl2BelowMinimum'      => true,
                        ]
                ]
            ],
            [
                [
                    "input"  =>
                        [
                            'gradientControl1' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'gradientControl2' => BrakeTestResultClass1And2ViewModel::EFFICIENCY_BETWEEN_30_AND_25,
                            'vehicleClass'     => VehicleClassCode::CLASS_1
                        ],
                    "output" =>
                        [
                            'gradientControl1AboveUpperMinimum' => false,
                            'gradientControl1BelowMinimum'      => false,
                            'gradientControl2AboveUpperMinimum' => false,
                            'gradientControl2BelowMinimum'      => false,
                        ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider gradientTestsForClass12
     */
    public function testGradientBrakeTestResultsPostClass12($testCase)
    {
        $motTestNumber = 1;
        $motTestData = $this->getMotTestDataDto(VehicleClassCode::CLASS_1);

        $this->routeMatch->setParam('action', 'addBrakeTestResults');
        $this->routeMatch->setParam('motTestNumber', $motTestNumber);

        $queryData = [
            'isSidecarAttached' => '0',
            'brakeTestType'     => BrakeTestTypeCode::GRADIENT,
        ];
        $this->brakeTestConfigurationContainerMock->expects($this->any())
            ->method('fetchConfig')
            ->willReturn($queryData);

        $postData = $testCase['input'];
        $this->setPostAndPostParams($postData);

        $expectedRestPostData = array_replace_recursive(
            [
                'isSidecarAttached'     => false,
                'brakeTestType'         => BrakeTestTypeCode::GRADIENT,
                'sidecarWeight'         => null,
                'control1EffortSidecar' => null,
                'control2EffortSidecar' => null,
                'control2EffortFront'   => null,
                'control2EffortRear'    => null,
                'vehicleWeightFront'    => null,
                'vehicleWeightRear'     => null,
                'riderWeight'           => null,
                'control1EffortFront'   => null,
                'control1EffortRear'    => null,
                '_class'                => BrakeTestConfigurationClass1And2Dto::class,
            ],
            $testCase['output']
        );

        $this->getRestClientMockWithGetMotTest($motTestData, true);

        $this->getBrakeTestResultsResourcesMock()
            ->expects($this->once())
            ->method('save')
            ->with($motTestNumber, $expectedRestPostData);

        $this->controller->dispatch($this->request);

        $this->assertRedirectLocation2("/mot-test/$motTestNumber/brake-test-summary");
    }

    protected function getMotTestData($vehicleClass = VehicleClassCode::CLASS_4, $motTestNumber = 1, $userId = 1, $status = 'ACTIVE')
    {
        $motTest = $this->jsonFixture('mot-test', __DIR__);

        $result = array_replace_recursive(
            $motTest['data'],
            [
                'motTestNumber' => $motTestNumber,
                'status'        => $status,
                'vehicle'       => [
                    'vehicleClass'  => [
                        'id'   => $vehicleClass,
                        'code' => $vehicleClass,
                    ],
                    'firstUsedDate' => '2001-02-02',
                ],
                'tester'        => [
                    'userId' => $userId,
                ],
            ]
        );

        return ['data' => $result];
    }

    protected function getMotTestDataDto($vehicleClass = VehicleClassCode::CLASS_4, $motTestNumber = 1, $userId = 1, $status = 'ACTIVE')
    {
        $motTest = (new MotTestDto())
            ->setMotTestNumber($motTestNumber)
            ->setStatus($status)
            ->setVehicle(
                (new VehicleDto())
                    ->setVehicleClass(
                        (new VehicleClassDto())
                            ->setId($vehicleClass)
                            ->setCode($vehicleClass)
                    )
                    ->setFirstUsedDate('2001-02-02')
            )
            ->setTester((new PersonDto())->setId($userId));

        return ['data' => $motTest];
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
}
