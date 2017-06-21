<?php

namespace OrganisationTest\Factory\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Controller\SiteController;
use Organisation\Factory\Controller\SiteControllerFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteControllerFactoryTest.
 */
class SiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager->setService('AuthorisationService', $authorisationService);

        $mapperFactory = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $mapperFactory);

        //  $identity = XMock::of(MotIdentityProviderInterface::class);
        //  $serviceManager->setService('MotIdentityProvider', $identity);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        //  --  check   --
        $this->assertInstanceOf(
            SiteController::class,
            (new SiteControllerFactory())->createService($plugins)
        );
    }
}
