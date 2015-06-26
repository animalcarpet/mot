<?php

namespace DvsaMotTestTest\Factory\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\TesterMotTestLogController;
use DvsaMotTest\Factory\Controller\TesterMotTestLogControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class TesterMotTestLogControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager->setService("AuthorisationService", $authorisationService);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        //  --  check   --
        $this->assertInstanceOf(
            TesterMotTestLogController::class,
            (new TesterMotTestLogControllerFactory())->createService($plugins)
        );
    }
}