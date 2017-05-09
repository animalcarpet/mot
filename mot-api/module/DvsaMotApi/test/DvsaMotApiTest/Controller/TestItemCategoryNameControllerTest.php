<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaMotApi\Controller\TestItemCategoryNameController;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * Test for TestItemSelectorController.
 */
class TestItemCategoryNameControllerTest extends AbstractMotApiControllerTestCase
{
    const MOT_TEST_NUMBER = '1';
    const TEST_ITEM_SELECTOR_ID = 5000;
    const TEST_ITEM_SELECTOR_INVAILD_ID = 999;
    const VEHILCE_CLASS_CODE = 4;
    const VEHILCE_CLASS_INVALID_CODE = 8888;

    private $vehicle;

    protected function setUp()
    {
        $this->vehicle = (new VehicleDto())
            ->setVehicleClass(
                (new VehicleClassDto())->setCode('1')
            );

        $this->setController(new TestItemCategoryNameController());
        parent::setUp();
    }

    public function testGetCategoriesName()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        //  --  define mock for tested service  --
        $mockTestItemSelectorService = $this->getMockItemSelectorService();
        $returnValue = [];
        $mockTestItemSelectorService->expects($this->once())
            ->method('getCurrentNonEmptyTestItemCategoryNamesByMotTest')
            ->willReturn($returnValue);

        //  --  define mock for other services  --
        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn(
                [
                    'vehicle' => (new VehicleDto())->setVehicleClass(
                        (new VehicleClassDto())->setCode('1')
                    ),
                ]
            );

        $method = 'get';
        $params = ['motTestNumber' => self::MOT_TEST_NUMBER];
        $this->getResultForAction($method, null, $params);

        $this->assertResponseStatus(200);
    }

    private function getMockItemSelectorService()
    {
        return $this->getMockServiceManagerClass(
            'TestItemSelectorService', TestItemSelectorService::class
        );
    }
}
