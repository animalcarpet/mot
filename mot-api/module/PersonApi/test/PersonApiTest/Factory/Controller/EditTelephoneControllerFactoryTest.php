<?php

namespace PersonApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\EditTelephoneController;
use PersonApi\Factory\Controller\EditTelephoneControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use PersonApi\Service\TelephoneService;
use Doctrine\ORM\EntityManager;

class EditTelephoneControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(TelephoneService::class);
        $serviceManager->setService(TelephoneService::class, $service);

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        /** @var ServiceLocatorInterface $plugins */
        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new EditTelephoneControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EditTelephoneController::class, $factoryResult);
    }
}

    {

}
