<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\MOT\Frontend\RegistrationModule\Controller\CompletedController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\CompletedControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class CompleteRegistrationControllerFactoryTest.
 */
class CompletedControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(RegistrationStepService::class);
        $serviceManager->setService(RegistrationStepService::class, $service);
        $serviceManager->setService(RegisterUserService::class, XMock::of(RegisterUserService::class));
        $serviceManager->setService(RegistrationSessionService::class, XMock::of(RegistrationSessionService::class));
        $mockConfig = [
            'helpdesk' => [
                'mockKey' => 'mockValue',
            ],
        ];
        $serviceManager->setService('Config', $mockConfig);

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new CompletedControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(CompletedController::class, $factoryResult);
    }
}
