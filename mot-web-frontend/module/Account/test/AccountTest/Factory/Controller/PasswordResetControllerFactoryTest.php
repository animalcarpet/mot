<?php

namespace AccountTest\Factory\Controller;

use Account\Controller\PasswordResetController;
use Account\Factory\Controller\PasswordResetControllerFactory;
use DvsaClient\MapperFactory;
use DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\XMock;
use Account\Service\PasswordResetService;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordResetControllerFactoryTest.
 */
class PasswordResetControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $passwordResetService = XMock::of(PasswordResetService::class);
        $serviceManager->setService(PasswordResetService::class, $passwordResetService);

        $userAdminSessionManager = XMock::of(UserAdminSessionManager::class);
        $serviceManager->setService(UserAdminSessionManager::class, $userAdminSessionManager);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        $obfuscator = XMock::of(ParamObfuscator::class);
        $serviceManager->setService(ParamObfuscatorFactory::class, $obfuscator);

        $serviceManager->setService('config', []);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordResetControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordResetController::class, $factoryResult);
    }
}
