<?php

namespace VehicleApiTest\Controller;

use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;
use VehicleApi\Controller\VehicleDvlaController;
use VehicleApi\Service\VehicleService;

/**
 * Test class VehicleController.
 */
class VehicleDvsaControllerTest extends AbstractMotApiControllerTestCase
{
    private static $VEHICLE_ID = 999;
    private static $VEHICLE_INVAILD_ID = 888;

    protected function setUp()
    {
        $this->setController(new VehicleDvlaController());
        parent::setUp();

        $loggerMock = $this->getMockWithDisabledConstructor(\Zend\Log\Logger::class);
        $this->serviceManager->setService('Application/Logger', $loggerMock);
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
        $this->mockValidAuthorization();

        //  --  mock    --
        $mockVehicleService = $this->getMockVehicleService();
        $mockMethod = $mockVehicleService->expects($this->once())
            ->method($serviceMethod);

        if ($serviceReturn instanceof \Exception) {
            $mockMethod->willThrowException($serviceReturn);
        } else {
            $mockMethod->willReturn($serviceReturn);
        }

        //  --  request --
        if ($serviceReturn instanceof \Exception) {
            $this->setExpectedException(
                get_class($serviceReturn),
                $expectResult['exceptionMsg'],
                $expectResult['exceptionCode']
            );
        }

        $result = $this->getResultForAction($method, $action, $params);

        //  --  check --
        if (isset($expectResult['exception'])) {
            $error = $result['errors'][0];

            $this->assertResponseStatusAndResultHasError(
                $this->getController()->getResponse(),
                $expectResult['statusCode'],
                $result,
                $error['message'],
                $error['code']
            );
        } else {
            $this->assertResponseStatusAndResult($expectResult['statusCode'], $expectResult['result'], $result);
        }
    }

    public function dataProviderTestCanAccessed()
    {
        $hydrator = new DtoHydrator();

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass((new VehicleClass())->setCode('A'));

        $vehicleEntity = new Vehicle();
        $vehicleEntity
            ->setId(self::$VEHICLE_ID)
            ->setModelDetail($modelDetail);

        $vehicleDto = (new VehicleDto())
            ->setId(self::$VEHICLE_ID)
            ->setVehicleClass(
                (new VehicleClassDto())->setCode('A')
            );

        return [
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'id' => self::$VEHICLE_ID,
                ],
                'serviceMethod' => 'getDvlaVehicleData',
                'serviceReturn' => $vehicleDto,

                'expectResult' => [
                    'statusCode' => self::HTTP_OK_CODE,
                    'result' => ['data' => $hydrator->extract($vehicleDto)],
                ],
            ],
            [
                'method' => 'get',
                'action' => null,
                'params' => [
                    'id' => self::$VEHICLE_ID,
                ],
                'serviceMethod' => 'getDvlaVehicleData',
                'serviceReturn' => new NotFoundException('Vehicle', self::$VEHICLE_INVAILD_ID),

                'expectResult' => [
                    'statusCode' => self::HTTP_ERR_400,
                    'exceptionCode' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    'exceptionMsg' => 'Vehicle '.self::$VEHICLE_INVAILD_ID.' not found',
                ],
            ],
        ];
    }

    public function testGetAuthorisedExaminerSearchService()
    {
        $this->assertEquals(
            $this->getMockVehicleService(),
            XMock::invokeMethod($this->getController(), 'getVehicleService')
        );
    }

    private function getMockVehicleService()
    {
        return $this->getMockServiceManagerClass(VehicleService::class, VehicleService::class);
    }
}
