<?php

namespace DvsaMotApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\MotConfig\ElasticsearchConfigKeys;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemCategoryDescription;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\RfrRepository;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * Class TestItemSelectorServiceTest.
 */
class TestItemSelectorServiceTest extends AbstractMotTestServiceTest
{
    private $testMotTestNumber = '17';
    private $vehicleClass = VehicleClassCode::CLASS_4;
    private $testItemSelector;
    private $determinedRole = SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG;

    private $mockTestItemCategoryRepository;
    private $mockRfrRepository;

    /**
     * @var DefectSentenceCaseConverter
     */
    private $defectSentenceCaseConverter;
    private $motConfig;

    public function setUp()
    {
        $this->testItemSelector = $this->getTestItemSelector();

        $this->mockTestItemCategoryRepository
            = $this->getMockWithDisabledConstructor(TestItemCategoryRepository::class);

        $this->mockRfrRepository = $this->getMockWithDisabledConstructor(RfrRepository::class);

        $this->defectSentenceCaseConverter = $this
            ->getMockBuilder(DefectSentenceCaseConverter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->motConfig = XMock::of(MotConfig::class);
        $this->motConfig->method('get')->willReturn([
            ElasticsearchConfigKeys::ES_INDEX_NAME => 'index'
            ]);
    }

    public function testGetTestItemSelectorsDataByClass()
    {
        $testItemSelectorId = TestItemSelectorService::ROOT_SELECTOR_ID;

        $expectedTisHydratorData = $this->getTestArrayWithId($testItemSelectorId);
        $expectedData = $this->getExpectedData($expectedTisHydratorData, [$expectedTisHydratorData], [], []);

        $mockEntityManager = $this->getMockEntityManager();

        $mockHydrator = $this->getMockHydrator();
        $mockHydrator->expects($this->any())
            ->method('extract')
            ->with($this->testItemSelector)
            ->will($this->returnValue($expectedTisHydratorData));

        $this->mockTestItemCategoryRepository->expects($this->any())
            ->method('findByIdAndVehicleClass')
            ->with(TestItemSelectorService::ROOT_SELECTOR_ID, $this->vehicleClass)
            ->will($this->returnValue([$this->testItemSelector]));
        $this->mockTestItemCategoryRepository->expects($this->any())
            ->method('findByVehicleClass')
            ->with($this->vehicleClass)
            ->will($this->returnValue([$this->testItemSelector]));

        $testItemSelectorService = $this->getTisServiceWithMocks($mockEntityManager, $mockHydrator);

        //when
        $result = $testItemSelectorService->getTestItemSelectorsDataByClass($this->vehicleClass);

        //then
        $this->assertEquals($expectedData, $result);
    }

    public function testGetTestItemSelectorsData()
    {
        //given
        $testItemSelectorId = 0;

        $reasonForRejection = (new ReasonForRejection())
            ->setDescriptions([]);
        $reasonsForRejection = [$reasonForRejection];

        $expectedTisHydratorData = $this->getTestArrayWithId($testItemSelectorId);

        $expectedData = [];
        $expectedData[] = $this->getExpectedData(
            $expectedTisHydratorData,
            [$expectedTisHydratorData],
            [$expectedTisHydratorData],
            [$expectedTisHydratorData]
        );

        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManagerHandler = new MockHandler($mockEntityManager, $this);

        $this->mockTestItemCategoryRepository->expects($this->any())
            ->method('findByIdAndVehicleClass')
            ->with($testItemSelectorId, $this->vehicleClass)
            ->will($this->returnValue([$this->testItemSelector]));
        $this->mockTestItemCategoryRepository->expects($this->any())
            ->method('findByParentIdAndVehicleClass')
            ->with($testItemSelectorId, $this->vehicleClass)
            ->will($this->returnValue([$this->testItemSelector]));

        $this->mockRfrRepository->expects($this->once())
            ->method('findByIdAndVehicleClassForUserRole')
            ->with($testItemSelectorId, $this->vehicleClass, $this->determinedRole)
            ->will($this->returnValue($reasonsForRejection));

        $mockEntityManagerHandler->next('find')
            ->will($this->returnValue($this->getTestItemSelector(0)));

        $mockHydrator = $this->getMockHydrator();
        $mockHydratorHandler = new MockHandler($mockHydrator, $this);
        $mockHydratorHandler
            ->next('extract')
            ->with($this->testItemSelector)
            ->will($this->returnValue($expectedTisHydratorData));
        $mockHydratorHandler
            ->next('extract')
            ->with($this->getTestItemSelector(0))
            ->will($this->returnValue($expectedTisHydratorData));
        $mockHydratorHandler
            ->next('extract')
            ->with($this->testItemSelector)
            ->will($this->returnValue($expectedTisHydratorData));
        $mockHydratorHandler
            ->next('extract')
            ->with($reasonForRejection)
            ->will($this->returnValue($expectedTisHydratorData));

        $testItemSelectorService = $this->getTisServiceWithMocks($mockEntityManager, $mockHydrator);

        //when
        $result = $testItemSelectorService->getTestItemSelectorsData($testItemSelectorId, $this->vehicleClass);

        //then
        $this->assertEquals($expectedData, $result);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionMessage Test Item Selector 999 not found
     */
    public function testGetTestItemSelectorsDataThrowsNotFoundException()
    {
        //given
        $invalidTestItemSelectorId = 999;
        $testItemSelectors = [];

        $mockEntityManager = $this->getMockEntityManager();

        $this->mockTestItemCategoryRepository->expects($this->any())
            ->method('findByIdAndVehicleClass')
            ->with($invalidTestItemSelectorId, $this->vehicleClass)
            ->will($this->returnValue($testItemSelectors));

        $mockHydrator = $this->getMockHydrator();

        $testItemSelectorService = $this->getTisServiceWithMocks($mockEntityManager, $mockHydrator);

        //when
        $testItemSelectorService->getTestItemSelectorsData($invalidTestItemSelectorId, $this->vehicleClass);
        //then exception
    }

    public function test_search_thorwsExcption_whenUserHasNotGotPermissionForReadingRfr()
    {

    }

    protected function getTestMotTest()
    {
        $motTest = (new MotTest())->setId($this->testMotTestNumber);
        $vehicle = new Vehicle();
        $vehicle->setVehicleClass(new VehicleClass($this->vehicleClass));
        $motTest->setVehicle($vehicle);

        return $motTest;
    }

    protected function getTestArrayWithId($motTestId = 17)
    {
        return ['id' => $motTestId, 'parentTestItemSelectorId' => 0, 'vehicleClasses' => []];
    }

    protected function getExpectedData($tis, $tises, $tisRfrs, $parentTises)
    {
        return [
            'testItemSelector' => $tis,
            'parentTestItemSelectors' => $parentTises,
            'testItemSelectors' => $tises,
            'reasonsForRejection' => $tisRfrs,
        ];
    }

    protected function getTisServiceWithMocks($mockEntityManager, $mockHydrator, $mockAuthService = null,
                                              $disabledRfrs = [])
    {
        $mockAuthService = $mockAuthService ?: $this->getMockAuthorizationService();

        return new TestItemSelectorService(
            $mockEntityManager,
            $mockHydrator,
            $this->mockRfrRepository,
            $mockAuthService,
            $this->mockTestItemCategoryRepository,
            $disabledRfrs,
            $this->defectSentenceCaseConverter,
            $this->motConfig
        );
    }

    protected function getTestItemSelector($id = 5, $parentId = 0)
    {
        return (new TestItemSelector())
            ->setId($id)
            ->setParentTestItemSelectorId($parentId)
            ->setDescriptions(new ArrayCollection([new TestItemCategoryDescription()]));
    }
}
