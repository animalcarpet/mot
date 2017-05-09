<?php

namespace AccountTest\Factory\Controller;

use Account\Controller\ClaimController;
use Account\Factory\Controller\ClaimAccountControllerFactory;
use Account\Service\ClaimAccountService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Mvc\Controller\ControllerManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

class ClaimAccountControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $mockClaimAccountSrv = XMock::of(ClaimAccountService::class);
        $serviceManager->setService(ClaimAccountService::class, $mockClaimAccountSrv);

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $serviceManager->setService('MotIdentityProvider', XMock::of(MotIdentityProviderInterface::class));

        $factory = new ClaimAccountControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(ClaimController::class, $factoryResult);
    }
}
