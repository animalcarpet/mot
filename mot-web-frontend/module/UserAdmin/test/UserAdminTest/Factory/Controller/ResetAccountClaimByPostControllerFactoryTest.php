<?php

namespace UserAdminTest\Factory\Controller;

use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use UserAdmin\Controller\ResetAccountClaimByPostController;
use UserAdmin\Factory\Controller\ResetAccountClaimByPostControllerFactory;
use UserAdmin\Service\HelpdeskAccountAdminService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ResetAccountClaimByPostControllerFactoryTest.
 */
class ResetAccountClaimByPostControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $userAccountAdminService = XMock::of(HelpdeskAccountAdminService::class);
        $serviceManager->setService(HelpdeskAccountAdminService::class, $userAccountAdminService);

        $testerQualificationStatus = XMock::of(TesterGroupAuthorisationMapper::class);
        $serviceManager->setService(TesterGroupAuthorisationMapper::class, $testerQualificationStatus);

        $authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $serviceManager->setService('AuthorisationService', $authorisationService);

        // Create the factory
        $factory = new ResetAccountClaimByPostControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(ResetAccountClaimByPostController::class, $factoryResult);
    }
}
