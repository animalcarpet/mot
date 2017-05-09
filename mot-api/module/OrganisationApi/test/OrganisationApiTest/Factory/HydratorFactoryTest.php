<?php

namespace OrganisationApiTest\Factory;

use DvsaCommon\Utility\Hydrator;
use OrganisationApi\Factory\HydratorFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class HydratorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $factory = new HydratorFactory();
        $serviceLocator = new ServiceManager();
        $service = $factory->createService($serviceLocator);
        $this->assertInstanceOf(Hydrator::class, $service);
    }
}
