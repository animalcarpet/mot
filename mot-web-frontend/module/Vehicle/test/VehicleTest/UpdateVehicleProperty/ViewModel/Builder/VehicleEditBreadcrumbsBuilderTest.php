<?php

namespace VehicleTest\UpdateVehicleProperty\ViewModel\Builder;

use DvsaCommonTest\TestUtils\XMock;
use Vehicle\UpdateVehicleProperty\ViewModel\Builder\VehicleEditBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class VehicleEditBreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 'obfuscatedVehId';
    const QUERY_PARAM_VALUE = 'value';
    const PAGE_TITLE = 'title';

    public function testBreadcrumbsGeneration()
    {
        $urlHelperPlugin = XMock::of(Url::class);
        $urlHelperPlugin->expects($this->once())->method('__invoke')->willReturnCallback(function($route, $params, $queryParams){
            $this->assertEquals($params['id'], self::VEHICLE_ID);
            $this->assertEquals('vehicle/detail', $route);
            $this->assertContains(self::QUERY_PARAM_VALUE, $queryParams['query']);
        });

        $builder = new VehicleEditBreadcrumbsBuilder($urlHelperPlugin);
        $breadcrumbs = $builder->getVehicleEditBreadcrumbs(self::PAGE_TITLE, self::VEHICLE_ID, ['param' => self::QUERY_PARAM_VALUE]);


        $this->assertEquals(self::PAGE_TITLE, array_keys($breadcrumbs)[1]);
    }
}