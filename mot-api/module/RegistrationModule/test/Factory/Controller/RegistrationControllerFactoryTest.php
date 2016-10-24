<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Api\RegistrationModule\Controller\RegistrationController;
use Dvsa\Mot\Api\RegistrationModule\Factory\Controller\RegistrationControllerFactory;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * class RegistrationControllerFactory.
 */
class RegistrationControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        // Mock dependencies
        $serviceManager = new ServiceManager();

        $serviceManager->setService(
            RegistrationService::class,
            XMock::of(RegistrationService::class)
        );

        /** @var ControllerManager $plugins */
        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));
        // Create the factory
        $factory = new RegistrationControllerFactory();

        $this->assertInstanceOf(
            RegistrationController::class,
            $factory->createService($plugins)
        );
    }
}
