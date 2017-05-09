<?php

namespace VehicleApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use VehicleApi\Controller\VehicleController;
use Zend\View\Model\JsonModel;
use VehicleApi\Service\VehicleSearchService;

/**
 * Test class VehicleController.
 */
class VehicleControllerTest extends PHPUnit_Framework_TestCase
{
    const validVehicleId = 999;

    protected $searchServiceMock;
    protected $vehicleDtoMock;

    public function setUp()
    {
        parent::setUp();

        $this->searchServiceMock = $this->getMockBuilder('VehicleApi\Service\VehicleService')
            ->disableOriginalConstructor()
            ->setMethods(['getVehicleDto'])
            ->getMock();

        $this->vehicleDtoMock = $this->getMockBuilder('DvsaCommon\Dto\Vehicle\VehicleDto')
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchServiceMock
            ->expects($this->once())
            ->method('getVehicleDto')
            ->will($this->returnValue($this->vehicleDtoMock));
    }

    public function testController()
    {
        $controller = new VehicleController($this->searchServiceMock, XMock::of(VehicleSearchService::class));

        $result = $controller->get(self::validVehicleId);
        $this->assertInstanceOf(JsonModel::class, $result);
    }
}
