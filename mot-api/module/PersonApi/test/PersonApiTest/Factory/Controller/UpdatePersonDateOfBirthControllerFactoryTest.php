<?php

namespace PersonApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\UpdatePersonDateOfBirthController;
use PersonApi\Factory\Controller\UpdatePersonDateOfBirthControllerFactory;
use PersonApi\Service\PersonDateOfBirthService;
use Zend\ServiceManager\ServiceManager;

class UpdatePersonDateOfBirthControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $updatePersonDOBService = XMock::of(PersonDateOfBirthService::class);
        $serviceManager->setService(PersonDateOfBirthService::class, $updatePersonDOBService);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new UpdatePersonDateOfBirthControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(UpdatePersonDateOfBirthController::class, $factoryResult);
    }
}
