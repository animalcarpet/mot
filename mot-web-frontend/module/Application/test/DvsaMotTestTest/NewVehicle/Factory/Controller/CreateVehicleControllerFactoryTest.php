<?php
namespace DvsaMotTestTest\Factory\Controller;

use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\NewVehicle\Controller\CreateVehicleController;
use DvsaMotTest\NewVehicle\Controller\Factory\CreateVehicleControllerFactory;
use Zend\Http\Request;
use Application\Service\ContingencySessionManager;
use Application\Service\CanTestWithoutOtpService;
use DvsaMotTest\NewVehicle\Form\VehicleWizard\CreateVehicleFormWizard;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;


class CreateVehicleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $request = new Request();
        $authService = new AuthorisationServiceMock();
        $wizard = XMock::of(CreateVehicleFormWizard::class);
        $contingencySessionManager = XMock::of(ContingencySessionManager::class);
        $canTestWithoutOtpService = XMock::of(CanTestWithoutOtpService::class);

        $serviceManager->setService('Request', $request);
        $serviceManager->setService('AuthorisationService', $authService);
        $serviceManager->setService(CreateVehicleFormWizard::class, $wizard);
        $serviceManager->setService(ContingencySessionManager::class, $contingencySessionManager);
        $serviceManager->setService(CanTestWithoutOtpService::class, $canTestWithoutOtpService);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new CreateVehicleControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(CreateVehicleController::class, $factoryResult);
    }
}
