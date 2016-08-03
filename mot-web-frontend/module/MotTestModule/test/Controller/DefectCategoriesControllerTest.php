<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;
use DvsaEntities\Entity\VehicleClass;
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

    protected function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
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

    public function testIndex()
    {
        $motTestNumber = 1;
        $testItemSelectorId = 502;

        $vehicleMock = $this->getMockBuilder(VehicleDto::class)->getMock();
        $vehicleMock
            ->expects($this->once())
            ->method('getMakeAndModel')
            ->willReturn('Piaggio, Typhoon');
        $vehicleMock
            ->expects($this->once())
            ->method('getFirstUsedDate')
            ->willReturn('2011-07-01');

        $vehicleClassMock = $this->getMockBuilder(VehicleClassDto::class)->getMock();
        $vehicleClassMock->expects($this->any())
            ->method('getCode')
            ->willReturn(VehicleClassCode::CLASS_1);

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);

        $this->motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicle')
            ->willReturn($vehicleMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber))
            ->willReturn(['data' => $this->motTestMock]);

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

        $vehicleMock = $this->getMockBuilder(VehicleDto::class)->getMock();
        $vehicleMock
            ->expects($this->once())
            ->method('getMakeAndModel')
            ->willReturn('Piaggio, Typhoon');
        $vehicleMock
            ->expects($this->once())
            ->method('getFirstUsedDate')
            ->willReturn('2011-07-01');

        $vehicleClassMock = $this
            ->getMockBuilder(VehicleClass::class)
            ->getMock();
        $vehicleClassMock
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('1');

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);

        $this->motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicle')
            ->willReturn($vehicleMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithoutRfrs()]);

        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber))
            ->willReturn(['data' => $this->motTestMock]);

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

        $vehicleMock = $this->getMockBuilder(VehicleDto::class)->getMock();
        $vehicleMock
            ->expects($this->once())
            ->method('getMakeAndModel')
            ->willReturn('Piaggio, Typhoon');
        $vehicleMock
            ->expects($this->once())
            ->method('getFirstUsedDate')
            ->willReturn('2011-07-01');

        $vehicleClassMock = $this
            ->getMockBuilder(VehicleClassDto::class)
            ->getMock();
        $vehicleClassMock
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('1');

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);

        $this->motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicle')
            ->willReturn($vehicleMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber))
            ->willReturn(['data' => $this->motTestMock]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $testItemSelectorId,
        ];

        $this->getResultForAction('category', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function testCanRedirectToDefectCategoriesPage()
    {
        $this->markTestSkipped('Due to the route being removed when the FT is off this test is temporarily disabled '.
            'until the base test class supports FT awareness at Module bootstrap level.');

        $motTestNumber = 1;

        $this->withFeatureToggles([FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS => true]);

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

        $vehicleMock = $this->getMockBuilder(VehicleDto::class)->getMock();
        $vehicleMock
            ->expects($this->once())
            ->method('getMakeAndModel')
            ->willReturn('Piaggio, Typhoon');
        $vehicleMock
            ->expects($this->once())
            ->method('getFirstUsedDate')
            ->willReturn('2011-07-01');

        $vehicleClassMock = $this
            ->getMockBuilder(VehicleClassDto::class)
            ->getMock();
        $vehicleClassMock
            ->expects($this->any())
            ->method('getCode')
            ->willReturn('1');

        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn($motTestTypeCode);
        $this->motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicleClass')
            ->willReturn($vehicleClassMock);
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);
        $this->motTestMock
            ->expects($this->any())
            ->method('getVehicle')
            ->willReturn($vehicleMock);

        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock
            ->expects($this->any())
            ->method('getWithParamsReturnDto')
            ->willReturn(['data' => $this->getTestItemSelectorsWithRfrs()]);

        $restClientMock
            ->expects($this->at(0))
            ->method('get')
            ->with(MotTestUrlBuilder::motTest($motTestNumber))
            ->willReturn(['data' => $this->motTestMock]);

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
        return [
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
        ];
    }

    private function getTestItemSelectorsWithoutRfrs()
    {
        return [
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