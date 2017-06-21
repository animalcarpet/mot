<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\Mot\Frontend\RegistrationModule\Controller\PasswordController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\PasswordControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\PasswordService;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PasswordControllerFactoryTest.
 */
class PasswordControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $stepService = XMock::of(RegistrationStepService::class);
        $passwordService = XMock::of(PasswordService::class);
        $config = new MotConfig([]);

        $serviceManager->setService(RegistrationStepService::class, $stepService);
        $serviceManager->setService(PasswordService::class, $passwordService);
        $serviceManager->setService(MotConfig::class, $config);

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordController::class, $factoryResult);
    }
}
