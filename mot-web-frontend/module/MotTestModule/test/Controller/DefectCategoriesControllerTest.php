<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTestTest\TestHelper\Fixture;
use Zend\View\Model\ViewModel;

/**
 * Class DefectCategoriesControllerTest.
 */
class DefectCategoriesControllerTest extends AbstractFrontendControllerTestCase
{
    private $motTestMock;
    /**
     * @var AuthorisationService
     */
    private $authorisationServiceMock;

    /**
     * @var DefectsContentBreadcrumbsBuilder
     */
    private $defectsContentBreadcrumbsBuilderMock;

    protected $mockMotTestServiceClient;
    protected $mockVehicleServiceClient;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();

        $this->serviceManager->setService(
            MotTestService::class,
            $this->getMockMotTestServiceClient()
        );

        $this->serviceManager->setService(
            VehicleService::class,
            $this->getMockVehicleServiceClient()
        );

        $this->serviceManager->setAllowOverride(true);

        $this->authorisationServiceMock = $this
            ->getMockBuilder(MotAuthorisationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->defectsContentBreadcrumbsBuilderMock = $this
            ->getMockBuilder(DefectsContentBreadcrumbsBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new DefectCategoriesController($this->authorisationServiceMock, $this->defectsContentBreadcrumbsBuilderMock)
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

    private function getMockVehicleServiceClient()
    {
        if ($this->mockVehicleServiceClient == null) {
            $this->mockVehicleServiceClient = XMock::of(VehicleService::class);
        }
        return $this->mockVehicleServiceClient;
    }

    public function testIndex()
    {
        $motTestNumber = 1;
        $testItemSelectorId = 502;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        $this->getResultForAction('index', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCategoryWithoutRfrs()
    {
        $motTestNumber = 1;
        $testItemSelectorId = 502;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithoutRfrs()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        $this->getResultForAction('category', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCategoryAndDefectsForCategoryWithRfrs()
    {
        $motTestNumber = 1;
        $testItemSelectorId = 502;

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($testMotTestData));

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicleData));

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        $this->getResultForAction('category', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCanRedirectToDefectCategoriesPage()
    {
        $motTestNumber = 1;

        $this->getResultForAction('redirectToCategoriesIndex', ['motTestNumber' => $motTestNumber]);
        $this->assertRedirectLocation2("/mot-test/$motTestNumber/defects/categories");
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
        $motTestNumber = 1;
        $testItemSelectorId = 502;

        switch ($motTestTypeCode) {
            case MotTestTypeCode::NORMAL_TEST:
                $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
                $testMotTestData->testTypeCode = MotTestTypeCode::NORMAL_TEST;
                break;
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
                $testMotTestData->testTypeCode = MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
                break;
            case MotTestTypeCode::TARGETED_REINSPECTION:
                $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
                $testMotTestData->testTypeCode = MotTestTypeCode::TARGETED_REINSPECTION;
                break;
            default:
                $testMotTestData = Fixture::getMotTestDataVehicleClass4(true);
        }

        $motTest = new MotTest($testMotTestData);

        $mockMotTestServiceClient = $this->getMockMotTestServiceClient();
        $mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(1)
            ->will($this->returnValue($motTest));

        $vehicleData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $vehicle = new DvsaVehicle($vehicleData);

        $mockVehicleServiceClient = $this->getMockVehicleServiceClient();
        $mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(1001, 1)
            ->will($this->returnValue($vehicle));

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        $this->getResultForAction('index', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);

        /** @var ViewModel $layoutViewModel */
        $layoutViewModel = $this->controller->getPluginManager()->get('layout')->__invoke();
        $breadcrumbs = $layoutViewModel->getVariable('breadcrumbs');
        $this->assertArrayHasKey('breadcrumbs', $breadcrumbs);
        $breadcrumbs = $breadcrumbs['breadcrumbs'];

        $this->assertArrayHasKey($breadcrumbKey, $breadcrumbs);
        $this->assertEquals($breadcrumbValue, $breadcrumbs[$breadcrumbKey]);
    }

    private function getTestItemSelectorsWithRfrs()
    {
        return
            [
                [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 1,
                    'parentTestItemSelectorId' => 0,
                    'id' => 0,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                ],
                'reasonsForRejection' => [
                    1 => [
                        'rfrId' => 1,
                        'testItemSelectorId' => 1,
                        'testItemSelectorName' => 'sad',
                        'description' => 'asd',
                        'advisoryText' => 'asd',
                        'inspectionManualReference' => '2.1.2',
                        'isAdvisory' => true,
                        'isPrsFail' => false,
                        'canBeDangerous' => true,
                    ],
                ],
            ]
        ];
    }

    private function getTestItemSelectorsWithoutRfrs()
    {
        return [
                [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 0,
                    'parentTestItemSelectorId' => 0,
                    'id' => 0,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                ],
                'reasonsForRejection' => [

                ],
            ]
        ];
    }

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
}
