<?php

namespace VehicleApiTest\Controller;

use VehicleApi\Controller\VehicleSearchController;
use VehicleApi\Service\VehicleSearchService;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;

/**
 * Class VehicleSearchControllerTest.
 */
class VehicleSearchControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGivenInputValidationPassesSearchReturnsResults()
    {
        $searchService = $this->getMockBuilder(VehicleSearchService::class)
            ->disableOriginalConstructor()
            ->setMethods(['searchVehicleWithAdditionalData'])
            ->getMock();

        $vehicleSearchParam = new VehicleSearchParam('test', 'vin');

        $controller = new VehicleSearchController($searchService, $vehicleSearchParam);

        $result = $controller->getList();

        $this->assertInstanceOf('Zend\View\Model\JsonModel', $result);
    }
}
