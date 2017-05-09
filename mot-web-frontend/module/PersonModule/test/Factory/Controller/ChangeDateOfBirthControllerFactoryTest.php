<?php

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeDateOfBirthControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeDateOfBirthController;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\ServiceManager\ServiceManager;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;

class ChangeDateOfBirthControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $personProfileGuard = XMock::of(PersonProfileGuardBuilder::class);
        $serviceManager->setService(PersonProfileGuardBuilder::class, $personProfileGuard);

        $helpdeskAccountAdminService = XMock::of(HelpdeskAccountAdminService::class);
        $serviceManager->setService(HelpdeskAccountAdminService::class, $helpdeskAccountAdminService);

        $apiPersonalDetails = XMock::of(ApiPersonalDetails::class);
        $serviceManager->setService(ApiPersonalDetails::class, $apiPersonalDetails);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        $personProfileUrlGenerator = XMock::of(PersonProfileUrlGenerator::class);
        $serviceManager->setService(PersonProfileUrlGenerator::class, $personProfileUrlGenerator);

        $contextProvider = XMock::of(ContextProvider::class);
        $serviceManager->setService(ContextProvider::class, $contextProvider);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new ChangeDateOfBirthControllerFactory();
        $result = $factory->createService($plugins);

        $this->assertInstanceOf(ChangeDateOfBirthController::class, $result);
    }
}
