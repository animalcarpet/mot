<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\MOT\Frontend\RegistrationModule\Controller\ContactDetailsController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\ContactDetailsControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ContactDetailsControllerFactoryTest.
 */

class ContactDetailsControllerFactoryTest extends \PHPUnit_Framework_TestCase
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

        //Create Factory
        $factory = new ContactDetailsControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(ContactDetailsController::class, $factoryResult);
    }
}
