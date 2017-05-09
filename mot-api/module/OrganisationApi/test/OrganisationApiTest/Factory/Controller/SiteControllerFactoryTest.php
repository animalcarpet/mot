<?php

namespace OrganisationApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\SiteController;
use OrganisationApi\Factory\Controller\SiteControllerFactory;
use OrganisationApi\Service\SiteService;
use Zend\ServiceManager\ServiceManager;

class SiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(SiteService::class, XMock::of(SiteService::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteController::class, $factoryResult);
    }
}
