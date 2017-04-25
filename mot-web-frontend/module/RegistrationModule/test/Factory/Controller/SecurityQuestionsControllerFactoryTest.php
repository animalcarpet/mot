<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Controller\SecurityQuestionsController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\SecurityQuestionsControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SecurityQuestionsControllerFactoryTest.
 */

class SecurityQuestionsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service  =  XMock::of(RegistrationStepService::class);
        $serviceManager->setService(RegistrationStepService::class, $service);

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new SecurityQuestionsControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SecurityQuestionsController::class, $factoryResult);
    }
}

