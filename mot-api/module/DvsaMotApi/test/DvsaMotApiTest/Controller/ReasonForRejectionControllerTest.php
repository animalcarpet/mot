<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\ReasonForRejectionController;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestItemSelectorService;
use DvsaMotApiTest\Test\ReasonForRejectionBuilder;
use Zend\Stdlib\Parameters;

/**
 * Test for ReasonForRejectionController.
 */
class ReasonForRejectionControllerTest extends AbstractMotApiControllerTestCase
{
    const MOT_TEST_NUMBER = '1';

    /** @var  MotTestService | \PHPUnit_Framework_MockObject_MockObject */
    private $motTestService;

    /** @var  TestItemSelectorService | \PHPUnit_Framework_MockObject_MockObject */
    private $testItemSelectorService;

    protected function setUp()
    {
        $this->motTestService = $this->getMockMotTestService();
        $this->testItemSelectorService = $this->getMockItemSelectorService();

        $this->controller = new ReasonForRejectionController(
            $this->testItemSelectorService,
            $this->motTestService
        );
        parent::setUp();
    }

    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $searchString = 'stop lamp';

        $vehicle = (new VehicleDto())->setVehicleClass(
            (new VehicleClassDto())->setCode('1')
        );

        $expectedRfrs = ['rfrId' => 1];
        $expectedData = ['data' => (
            $expectedRfrs + ['motTest' => (new MotTestDto())
                ->setVehicle($vehicle)
                ->setReasonsForRejection(ReasonForRejectionBuilder::create()),
            ]),
        ];

        //  --  define mock for other services  --
        $this->motTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn((new MotTestDto())
                ->setVehicle($vehicle)
                ->setReasonsForRejection(ReasonForRejectionBuilder::create())
            );

        //  --  define mock for tested service  --
        $this->testItemSelectorService->expects($this->once())
                                    ->method('searchReasonsForRejection')
                                    ->with(self::MOT_TEST_NUMBER, $searchString)
                                    ->will($this->returnValue($expectedRfrs));

        $this->routeMatch->setParam('motTestNumber', self::MOT_TEST_NUMBER);
        $this->request->setQuery(
            new Parameters(
                [
                    ReasonForRejectionController::QUERY_PARAM_SEARCH => $searchString,
                ]
            )
        );

        $result = $this->controller->dispatch($this->request);

        $this->assertResponseStatusAndResult(self::HTTP_OK_CODE, DtoHydrator::dtoToJson($expectedData), $result);
    }

    public function testGetWithoutSearchParamReturnsBadRequest()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        //  --  define mock for other services  --
        $vehicle = (new VehicleDto())->setVehicleClass(
            (new VehicleClassDto())->setCode('1')
        );

        $this->motTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn((new MotTestDto())->setVehicle($vehicle));

        $result = $this->getResultForAction('get', null, ['motTestNumber' => self::MOT_TEST_NUMBER]);

        $this->assertResponseStatusAndResultHasError(
            $this->getController()->getResponse(),
            self::HTTP_ERR_400,
            $result,
            ReasonForRejectionController::SEARCH_REQUIRED_MESSAGE,
            ReasonForRejectionController::ERROR_CODE_REQUIRED
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | TestItemSelectorService
     */
    private function getMockItemSelectorService()
    {
        return $this->getMockBuilder(TestItemSelectorService::class)->disableOriginalConstructor()->getMock();
    }

    protected function getMockMotTestService()
    {
        return $this->getMockBuilder(MotTestService::class)->disableOriginalConstructor()->getMock();
    }
}
