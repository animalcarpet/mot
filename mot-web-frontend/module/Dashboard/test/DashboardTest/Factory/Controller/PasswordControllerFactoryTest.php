<?php

namespace DashboardTest\Factory\Controller;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use Dashboard\Factory\Controller\PasswordControllerFactory;
use Dashboard\Controller\PasswordController;
use Dashboard\Service\PasswordService;
use Core\Service\MotFrontendIdentityProviderInterface;
use Zend\ServiceManager\ServiceManager;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;

class PasswordControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $identity = XMock::of(MotFrontendIdentityInterface::class, ['getUsername']);
        $identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $config = new MotConfig([]);

        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $serviceManager->setService('MotIdentityProvider', $identityProvider);
        $serviceManager->setService(MotConfig::class, $config);

        $service = XMock::of(PasswordService::class);
        $serviceManager->setService(PasswordService::class, $service);

        $service = XMock::of(OpenAMClientInterface::class);
        $serviceManager->setService(OpenAMClientInterface::class, $service);

        $service = XMock::of(OpenAMClientOptions::class);
        $serviceManager->setService(OpenAMClientOptions::class, $service);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordController::class, $factoryResult);
    }
}
