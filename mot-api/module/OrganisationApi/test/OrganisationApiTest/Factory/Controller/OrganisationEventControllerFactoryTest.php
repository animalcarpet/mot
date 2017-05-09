<?php

namespace OrganisationApiTest\Factory\Controller;

use OrganisationApi\Controller\OrganisationEventController;
use OrganisationApi\Factory\Controller\OrganisationEventControllerFactory;
use OrganisationApi\Service\OrganisationEventService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\XMock;

class OrganisationEventControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     * @group orT
     */
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(OrganisationEventService::class, XMock::of(OrganisationEventService::class));

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new OrganisationEventControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(OrganisationEventController::class, $factoryResult);
    }
}
