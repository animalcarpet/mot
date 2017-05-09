<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\ReasonForRejectionController;
use DvsaMotApi\Service\TestItemSelectorService;
use DvsaMotApiTest\Test\ReasonForRejectionBuilder;
use Zend\Stdlib\Parameters;

/**
 * Test for ReasonForRejectionController.
 */
class ReasonForRejectionControllerTest extends AbstractMotApiControllerTestCase
{
    const MOT_TEST_NUMBER = '1';

    protected function setUp()
    {
        $this->controller = new ReasonForRejectionController();
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
        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn((new MotTestDto())
                ->setVehicle($vehicle)
                ->setReasonsForRejection(ReasonForRejectionBuilder::create())
            );

        //  --  define mock for tested service  --
        $mockTestItemSelectorService = $this->getMockItemSelectorService();
        $mockTestItemSelectorService->expects($this->once())
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

        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn(['vehicle' => $vehicle]);

        $result = $this->getResultForAction('get', null, ['motTestNumber' => self::MOT_TEST_NUMBER]);

        $this->assertResponseStatusAndResultHasError(
            $this->getController()->getResponse(),
            self::HTTP_ERR_400,
            $result,
            ReasonForRejectionController::SEARCH_REQUIRED_MESSAGE,
            ReasonForRejectionController::ERROR_CODE_REQUIRED
        );
    }

    public function testGetMotTestService()
    {
        $mockMethod = XMock::invokeMethod($this->getController(), 'getMotTestService');

        $mockService = $this->getMockMotTestService();

        $this->assertEquals($mockService, $mockMethod);
    }

    public function testGetTestItemSelectorService()
    {
        $mockMethod = XMock::invokeMethod($this->getController(), 'getTestItemSelectorService');

        $mockService = $this->getMockItemSelectorService();

        $this->assertEquals($mockService, $mockMethod);
    }

    private function getMockItemSelectorService()
    {
        return $this->getMockServiceManagerClass(
            'TestItemSelectorService', TestItemSelectorService::class
        );
    }
}
