<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\TestItemSelectorController;
use DvsaMotApi\Service\TestItemSelectorService;
use DvsaMotApiTest\Test\ReasonForRejectionBuilder;
use SebastianBergmann\Exporter\Exception;
use Zend\Stdlib\Parameters;

/**
 * Test for TestItemSelectorController.
 */
class TestItemSelectorControllerTest extends AbstractMotApiControllerTestCase
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

        $this->setController(new TestItemSelectorController());
        parent::setUp();
    }

    /**
     * Test access for specified action and parameters.
     *
     * @param string $method        HTTP request type (get, post, put)
     * @param string $action        Route action
     * @param array  $params        Route parameters
     * @param string $serviceMethod Service method name
     * @param string $serviceReturn Service method will return
     * @param array  $expectResult  Expected result
     *
     * @dataProvider dataProviderTestCanAccessed
     */
    public function testCanAccessed(
        $method,
        $action,
        $params,
        $serviceMethod,
        $serviceReturn,
        $expectResult
    ) {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        //  --  define mock for tested service  --
        $mockTestItemSelectorService = $this->getMockItemSelectorService();
        $mockMethod = $mockTestItemSelectorService->expects($this->once())
            ->method($serviceMethod);

        if ($serviceReturn instanceof \Exception) {
            $mockMethod->willThrowException($serviceReturn);
        } else {
            $mockMethod->willReturn($serviceReturn);
        }

        //  --  define mock for other services  --
        $mockMotTestService = $this->getMockMotTestService();
        $mockMotTestService->expects($this->any())
            ->method('getMotTestData')
            ->willReturn(
                    (new MotTestDto())
                        ->setVehicle((new VehicleDto())
                                ->setVehicleClass((new VehicleClassDto())
                                        ->setCode('1')
                                )
                        )
                        ->setReasonsForRejection(ReasonForRejectionBuilder::create())
            );

        //  --  define request and check  --
        if ($serviceReturn instanceof \Exception) {
            $this->setExpectedException(
                get_class($serviceReturn),
                $expectResult['exceptionMsg'],
                $expectResult['exceptionCode']
            );
        }

        $result = $this->getResultForAction($method, $action, $params);

        $this->assertResponseStatus($expectResult['statusCode']);
        if (isset($expectResult['exception'])) {
            $this->assertEquals($expectResult['exceptionCode'], $result['errors'][0]['code']);
            $this->assertEquals($expectResult['exceptionMsg'], $result['errors'][0]['message']);
        } else {
            $this->assertResponseStatusAndResult($expectResult['statusCode'], $expectResult['result'], $result);
        }
    }

    public function dataProviderTestCanAccessed()
    {
        $hydrator = new DtoHydrator();

        $vehicle = (new VehicleDto())
            ->setVehicleClass(
                (new VehicleClassDto())->setCode('1')
            );

        return [
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'motTestNumber' => self::MOT_TEST_NUMBER,
                    'tisId' => self::TEST_ITEM_SELECTOR_ID,
                ],
                'serviceMethod' => 'getTestItemSelectorsData',
                'serviceReturn' => [['someData' => '']],

                'expectResult' => [
                    'statusCode' => self::HTTP_OK_CODE,
                    'result' => [
                        'data' => $hydrator->dtoToJson(
                            [
                                [
                                    'someData' => '',
                                    'motTest' => (new MotTestDto())
                                        ->setVehicle($vehicle)
                                        ->setReasonsForRejection(ReasonForRejectionBuilder::create()),
                                ],
                            ]
                        ),
                    ],
                ],
            ],
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'motTestNumber' => self::MOT_TEST_NUMBER,
                    'tisId' => self::TEST_ITEM_SELECTOR_ID,
                ],
                'serviceMethod' => 'getTestItemSelectorsData',
                'serviceReturn' => new NotFoundException('Test Item Selector', self::TEST_ITEM_SELECTOR_INVAILD_ID),

                'expectResult' => [
                    'statusCode' => self::HTTP_ERR_400,
                    'exceptionCode' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    'exceptionMsg' => 'Test Item Selector '.self::TEST_ITEM_SELECTOR_INVAILD_ID.' not found',
                ],
            ],
        ];
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
