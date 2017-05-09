<?php

namespace OrganisationApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Controller\SiteLinkController;
use OrganisationApi\Factory\Controller\SiteLinkControllerFactory;
use OrganisationApi\Service\SiteLinkService;
use Zend\ServiceManager\ServiceManager;

class SiteLinkControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(SiteLinkService::class, XMock::of(SiteLinkService::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteLinkControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteLinkController::class, $factoryResult);
    }
}
