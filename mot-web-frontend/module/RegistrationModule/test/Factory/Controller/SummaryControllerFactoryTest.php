<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\MOT\Frontend\RegistrationModule\Controller\SummaryController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\SummaryControllerFactory;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SummaryControllerFactoryTest.
 */
class SummaryControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            RegistrationStepService::class,
            XMock::of(RegistrationStepService::class)
        )->setService(
            RegisterUserService::class,
            XMock::of(RegisterUserService::class)
        )->setService(
            'Config',
            ['helpdesk' => ['name' => 'something']]
        );

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new SummaryControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(SummaryController::class, $factoryResult);
    }
}
