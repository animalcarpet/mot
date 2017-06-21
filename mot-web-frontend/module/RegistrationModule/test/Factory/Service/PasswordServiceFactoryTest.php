<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Factory\Service\PasswordServiceFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\PasswordService;
use DvsaCommon\Validator\PasswordValidator;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

class PasswordServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(PasswordValidator::class);
        $serviceManager->setService(PasswordValidator::class, $service);

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PasswordServiceFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PasswordService::class, $factoryResult);
    }
}
