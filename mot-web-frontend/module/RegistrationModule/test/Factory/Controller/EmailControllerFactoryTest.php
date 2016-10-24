<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Controller\EmailController;
use Dvsa\Mot\Frontend\RegistrationModule\Factory\Controller\EmailControllerFactory;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationStepService;
use Dvsa\MOT\Frontend\RegistrationModule\Controller\DetailsController;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\IsEmailDuplicateService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

class EmailControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $stepServiceService  =  XMock::of(RegistrationStepService::class);
        $isEmailDuplicateService = XMock::of(IsEmailDuplicateService::class);
        $serviceManager->setService(RegistrationStepService::class, $stepServiceService);
        $serviceManager->setService(IsEmailDuplicateService::class, $isEmailDuplicateService);

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new EmailControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(EmailController::class, $factoryResult);
    }
}
