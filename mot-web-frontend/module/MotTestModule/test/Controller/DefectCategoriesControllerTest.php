<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCache;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTestTest\TestHelper\Fixture;
use Zend\View\Model\ViewModel;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use DvsaCommon\Constants\Role;

/**
 * Class DefectCategoriesControllerTest.
 */
class DefectCategoriesControllerTest extends AbstractFrontendControllerTestCase
{
    const DEFAULT_MOT_TEST_ID = 1;
    const DEFAULT_VEHICLE_ID = 1001;
    const DEFAULT_VEHICLE_VERSION = 1;
    const DEFAULT_TEST_ITEM_SELECTOR_ID = 502;

    /**
     * @var AuthorisationService | MockObject
     */
    private $authorisationServiceMock;

    /**
     * @var DefectsContentBreadcrumbsBuilder
     */
    private $defectsContentBreadcrumbsBuilderMock;

    /**
     * @var MotTestService | MockObject
     */
    protected $mockMotTestServiceClient;

    /**
     * @var VehicleService | MockObject
     */
    protected $mockVehicleServiceClient;

    /**
     * @var RfrCache | MockObject
     */
    private $rfrCacheMock;

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();

        $this->mockMotTestServiceClient = XMock::of(MotTestService::class);
        $this->mockVehicleServiceClient = XMock::of(VehicleService::class);
        $this->authorisationServiceMock = XMock::of(MotAuthorisationServiceInterface::class);
        $this->defectsContentBreadcrumbsBuilderMock = XMock::of(DefectsContentBreadcrumbsBuilder::class);
        $this->rfrCacheMock = XMock::of(RfrCache::class);

        $this->serviceManager->setAllowOverride(true);

        $this->serviceManager->setService(
            MotTestService::class,
            $this->mockMotTestServiceClient
        );

        $this->serviceManager->setService(
            VehicleService::class,
            $this->mockVehicleServiceClient
        );

        $this->serviceManager->setService(
            RfrCache::class,
            $this->rfrCacheMock
        );

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new DefectCategoriesController(
                $this->authorisationServiceMock,
                $this->defectsContentBreadcrumbsBuilderMock,
                $this->rfrCacheMock
            )
        );

        parent::setUp();
    }

    /**
     * @param bool $isVe
     * @param int  $invocationCount
     */
    private function withUserBeingVehicleExaminer($isVe = false, $invocationCount = 1)
    {
        $this->authorisationServiceMock
            ->expects($invocationCount == 1 ? $this->once() : $this->exactly($invocationCount))
            ->method('hasRole')
            ->with(Role::VEHICLE_EXAMINER)
            ->willReturn($isVe);
    }

    /**
     * @param bool $value
     * @param int  $invocationCount
     */
    private function withRfrCachingEnabled($value = true, $invocationCount = 1)
    {
        $this->rfrCacheMock
            ->expects($invocationCount == 1 ? $this->once() : $this->exactly($invocationCount))
            ->method('isEnabled')
            ->willReturn($value);
    }

    /**
     * @param null $return
     * @param int  $invocationCount
     */
    private function withGetItemOnRfrCache($return = null, $invocationCount = 1)
    {
        $this->rfrCacheMock
            ->expects($invocationCount == 1 ? $this->once() : $this->exactly($invocationCount))
            ->method('getItem')
            ->with($this->anything())
            ->willReturn($return);
    }

    /**
     * @param bool $return
     * @param int  $invocationCount
     */
    private function withSetItemOnRfrCache($return = true, $invocationCount = 1)
    {
        $this->rfrCacheMock
            ->expects($invocationCount == 1 ? $this->once() : $this->exactly($invocationCount))
            ->method('setItem')
            ->with($this->anything())
            ->willReturn($return);
    }

    /**
     * @dataProvider rfrCacheDP
     *
     * @param bool $isRfrCacheEnabled
     * @param bool $rfrCacheHit
     */
    public function testIndex($isRfrCacheEnabled, $rfrCacheHit)
    {
        $this->withUserBeingVehicleExaminer(false);
        $dataReturnedFromApi = $this->getTestItemSelectorsWithRfrs();
        $this->setRfrCacheExpectations($isRfrCacheEnabled, $rfrCacheHit, $dataReturnedFromApi);

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $this->withMotTestServiceReturningData($testMotTestData);
        $this->withVehicleServiceReturningData($vehicleData);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects(
                $this->shouldDataBeFetchedFromApi($isRfrCacheEnabled, $rfrCacheHit) === true ?
                    $this->once() :
                    $this->never()
            )
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $dataReturnedFromApi]);

        $routeParams = $this->createRouteParams();

        $this->getResultForAction('index', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function rfrCacheDP()
    {
        return [
            ['isRfrCacheEnabled' => true, 'rfrCacheHit' => true],
            ['isRfrCacheEnabled' => true, 'rfrCacheHit' => false],
            ['isRfrCacheEnabled' => false, 'rfrCacheHit' => false],
            ['isRfrCacheEnabled' => false, 'rfrCacheHit' => true],
        ];
    }

    /**
     * @dataProvider rfrCacheDP
     *
     * @param $isRfrCacheEnabled
     * @param $rfrCacheHit
     */
    public function testCategoryWithoutRfrs($isRfrCacheEnabled, $rfrCacheHit)
    {
        $this->withUserBeingVehicleExaminer(false);
        $dataReturnedFromApi = $this->getTestItemSelectorsWithoutRfrs();
        $this->setRfrCacheExpectations($isRfrCacheEnabled, $rfrCacheHit, $dataReturnedFromApi);

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $this->withMotTestServiceReturningData($testMotTestData);
        $this->withVehicleServiceReturningData($vehicleData);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects(
                $this->shouldDataBeFetchedFromApi($isRfrCacheEnabled, $rfrCacheHit) === true ?
                    $this->once() :
                    $this->never()
            )
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $dataReturnedFromApi]);

        $routeParams = $this->createRouteParams();

        $this->getResultForAction('category', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * @dataProvider rfrCacheDP
     *
     * @param $isRfrCacheEnabled
     * @param $rfrCacheHit
     */
    public function testCategoryAndDefectsForCategoryWithRfrs($isRfrCacheEnabled, $rfrCacheHit)
    {
        $this->withUserBeingVehicleExaminer(false);
        $dataReturnedFromApi = $this->getTestItemSelectorsWithRfrs();
        $this->setRfrCacheExpectations($isRfrCacheEnabled, $rfrCacheHit, $dataReturnedFromApi);

        $testMotTestData = new MotTest(Fixture::getMotTestDataVehicleClass1(true));
        $vehicleData = new DvsaVehicle(Fixture::getDvsaVehicleTestDataVehicleClass4(true));

        $this->withMotTestServiceReturningData($testMotTestData);
        $this->withVehicleServiceReturningData($vehicleData);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects(
                $this->shouldDataBeFetchedFromApi($isRfrCacheEnabled, $rfrCacheHit) === true ?
                    $this->once() :
                    $this->never()
            )
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $dataReturnedFromApi]);

        $routeParams = $this->createRouteParams();

        $this->getResultForAction('category', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCanRedirectToDefectCategoriesPage()
    {
        $motTestNumber = self::DEFAULT_MOT_TEST_ID;

        $this->getResultForAction('redirectToCategoriesIndex', ['motTestNumber' => $motTestNumber]);
        $this->assertRedirectLocation2("/mot-test/$motTestNumber/defects/categories");
    }

    /**
     * @dataProvider testBreadcrumbsDataProvider
     *
     * @param $breadcrumbKey
     * @param $breadcrumbValue
     * @param $motTestTypeCode
     * @param $isRfrCacheEnabled
     * @param $rfrCacheHit
     */
    public function testCorrectBreadcrumbsAreDisplayed($breadcrumbKey, $breadcrumbValue, $motTestTypeCode, $isRfrCacheEnabled, $rfrCacheHit)
    {
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
        $this->withMotTestServiceReturningData($motTest);

        $vehicleData = Fixture::getDvsaVehicleTestDataVehicleClass4(true);
        $vehicle = new DvsaVehicle($vehicleData);
        $this->withVehicleServiceReturningData($vehicle);

        $this->withUserBeingVehicleExaminer(false);
        $dataReturnedFromApi = $this->getTestItemSelectorsWithRfrs();
        $this->setRfrCacheExpectations($isRfrCacheEnabled, $rfrCacheHit, $dataReturnedFromApi);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects(
                $this->shouldDataBeFetchedFromApi($isRfrCacheEnabled, $rfrCacheHit) === true ?
                    $this->once() :
                    $this->never()
            )
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $dataReturnedFromApi]);

        $routeParams = $this->createRouteParams();

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
                        'deficiencyCategoryCode' => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE,
                        'isPreEuDirective' => true,
                    ],
                ],
            ],
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
            ],
        ];
    }

    public function testBreadcrumbsDataProvider()
    {
        return [
            [
                'MOT test results',
                '/mot-test/1',
                MotTestTypeCode::NORMAL_TEST,
                true,
                true,
            ],
            [
                'MOT test results',
                '/mot-test/1',
                MotTestTypeCode::NORMAL_TEST,
                true,
                false,
            ],
            [
                'MOT test results',
                '/mot-test/1',
                MotTestTypeCode::NORMAL_TEST,
                false,
                true,
            ],
            [
                'MOT test results',
                '/mot-test/1',
                MotTestTypeCode::NORMAL_TEST,
                false,
                false,
            ],
            [
                'Training test',
                '/mot-test/1',
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                true,
                true,
            ],
            [
                'Training test',
                '/mot-test/1',
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                true,
                false,
            ],
            [
                'Training test',
                '/mot-test/1',
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                false,
                true,
            ],
            [
                'Training test',
                '/mot-test/1',
                MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                false,
                false,
            ],
            [
                'MOT test reinspection',
                '/mot-test/1',
                MotTestTypeCode::TARGETED_REINSPECTION,
                true,
                true,
            ],
            [
                'MOT test reinspection',
                '/mot-test/1',
                MotTestTypeCode::TARGETED_REINSPECTION,
                true,
                false,
            ],
            [
                'MOT test reinspection',
                '/mot-test/1',
                MotTestTypeCode::TARGETED_REINSPECTION,
                false,
                true,
            ],
            [
                'MOT test reinspection',
                '/mot-test/1',
                MotTestTypeCode::TARGETED_REINSPECTION,
                false,
                false,
            ],
        ];
    }

    /**
     * @param $motTestNumber
     * @param $testItemSelectorId
     *
     * @return array
     */
    private function createRouteParams($motTestNumber = self::DEFAULT_MOT_TEST_ID, $testItemSelectorId = self::DEFAULT_TEST_ITEM_SELECTOR_ID)
    {
        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        return $routeParams;
    }

    /**
     * @param $testMotTestData
     */
    private function withMotTestServiceReturningData($testMotTestData)
    {
        $this->mockMotTestServiceClient
            ->expects($this->once())
            ->method('getMotTestByTestNumber')
            ->with(self::DEFAULT_MOT_TEST_ID)
            ->will($this->returnValue($testMotTestData));
    }

    /**
     * @param $vehicleData
     */
    private function withVehicleServiceReturningData($vehicleData)
    {
        $this->mockVehicleServiceClient
            ->expects($this->once())
            ->method('getDvsaVehicleByIdAndVersion')
            ->with(self::DEFAULT_VEHICLE_ID, self::DEFAULT_VEHICLE_VERSION)
            ->will($this->returnValue($vehicleData));
    }

    /**
     * @param bool $isRfrCacheEnabled
     * @param bool $rfrCacheHit
     *
     * @return int
     */
    private function expectedIsCacheEnabledInvocations($isRfrCacheEnabled, $rfrCacheHit)
    {
        if ($isRfrCacheEnabled === true && $rfrCacheHit === true) {
            return 1;
        }

        return 2;
    }

    /**
     * @param bool $isRfrCacheEnabled
     *
     * @return int
     */
    private function expectedGetItemInvocations($isRfrCacheEnabled)
    {
        return $isRfrCacheEnabled === true ? 1 : 0;
    }

    /**
     * @param bool $isRfrCacheEnabled
     * @param bool $rfrCacheHit
     *
     * @return int
     */
    private function expectedSetItemInvocations($isRfrCacheEnabled, $rfrCacheHit)
    {
        if ($isRfrCacheEnabled === true && $rfrCacheHit === false) {
            return 1;
        }

        return 0;
    }

    /**
     * @param $isRfrCacheEnabled
     * @param $rfrCacheHit
     * @param array $dataFromApi
     */
    private function setRfrCacheExpectations($isRfrCacheEnabled, $rfrCacheHit, array $dataFromApi)
    {
        $this->withRfrCachingEnabled(
            $isRfrCacheEnabled,
            $this->expectedIsCacheEnabledInvocations($isRfrCacheEnabled, $rfrCacheHit)
        );

        $this->withGetItemOnRfrCache(
            $rfrCacheHit == true ? $dataFromApi : null,
            $this->expectedGetItemInvocations($isRfrCacheEnabled)
        );

        $this->withSetItemOnRfrCache(
            true,
            $this->expectedSetItemInvocations($isRfrCacheEnabled, $rfrCacheHit)
        );
    }

    private function shouldDataBeFetchedFromApi($isRfrCacheEnabled, $rfrCacheHit)
    {
        if ($isRfrCacheEnabled === true && $rfrCacheHit === true) {
            return false;
        }

        return true;
    }
}
