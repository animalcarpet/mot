<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\MOT\Frontend\RegistrationModule\Controller\CreateAccountController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\CreateAccountControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class CreateAccountControllerFactoryTest.
 */
class CreateAccountControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(RegistrationStepService::class);
        $serviceManager->setService(RegistrationStepService::class, $service);
        $serviceManager->setService(RegistrationSessionService::class, XMock::of(RegistrationSessionService::class));

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new CreateAccountControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(CreateAccountController::class, $factoryResult);
    }
}
