<?php

namespace MailerApiTest\Factory\Service;

use MailerApi\Factory\Service\TemplateResolverServiceFactory;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;
use MailerApi\Service\TemplateResolverService;

class TemplateResolverServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new TemplateResolverServiceFactory();
        $service = $factory->createService($plugins);

        $this->assertInstanceOf(
            TemplateResolverService::class,
            $service
        );
    }
}
