<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Factory\Security;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeTelephoneController;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\ChangeTelephoneControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use UserAdmin\Service\UserAdminSessionService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class ChangeTelephoneControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $helpdeskAccountAdmin = XMock::of(HelpdeskAccountAdminService::class);
        $serviceManager->setService(HelpdeskAccountAdminService::class, $helpdeskAccountAdmin);

        $mapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $serviceManager->setService(TesterGroupAuthorisationMapper::class, $mapper);

        $contextProvider = XMock::of(ContextProvider::class);
        $serviceManager->setService(ContextProvider::class, $contextProvider);

        $userAdminSessionService = XMock::of(UserAdminSessionService::class);
        $serviceManager->setService(UserAdminSessionService::class, $userAdminSessionService);

        $personRoleManagementService = XMock::of(PersonRoleManagementService::class);
        $serviceManager->setService(PersonRoleManagementService::class, $personRoleManagementService);

        $personUrlGenerator = XMock::of(PersonProfileUrlGenerator::class);
        $serviceManager->setService(PersonProfileUrlGenerator::class, $personUrlGenerator);

        $personalDetailsService = XMock::of(ApiPersonalDetails::class);
        $serviceManager->setService(ApiPersonalDetails::class, $personalDetailsService);

        $personProfileGuardBuilder = Xmock::of(PersonProfileGuardBuilder::class);
        $serviceManager->setService(PersonProfileGuardBuilder::class, $personProfileGuardBuilder);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        /** @var ServiceLocatorInterface $plugins */
        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new ChangeTelephoneControllerFactory();
        $result = $factory->createService($plugins);

        $this->assertInstanceOf(ChangeTelephoneController::class, $result);
    }
}
