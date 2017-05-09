<?php

namespace VehicleTest\UpdateVehicleProperty\ViewModel\Builder;

use DvsaCommonTest\TestUtils\XMock;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class VehicleEditBreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 'obfuscatedVehId';
    const PAGE_TITLE = 'title';

    public function testBreadcrumbsGeneration()
    {
        $urlHelperPlugin = XMock::of(Url::class);
        $urlHelperPlugin->expects($this->at(0))->method('__invoke')->willReturnCallback(function ($route, $params, $queryParams) {
            $this->assertEquals('vehicle/search', $route);
        });

        $urlHelperPlugin->expects($this->at(1))->method('__invoke')->willReturnCallback(function ($route, $params, $queryParams) {
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail', $route);
        });

        $builder = new VehicleEditBreadcrumbsBuilder($urlHelperPlugin);
        $breadcrumbs = $builder->getVehicleEditBreadcrumbs(self::PAGE_TITLE, self::VEHICLE_ID);

        $this->assertEquals('Vehicle search', array_keys($breadcrumbs)[0]);
        $this->assertEquals('Vehicle', array_keys($breadcrumbs)[1]);
        $this->assertEquals(self::PAGE_TITLE, array_keys($breadcrumbs)[2]);
    }
}
