<?php
namespace SiteTest\Factory\Controller;

use Application\Service\CatalogService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Site\Controller\SiteController;
use Site\Factory\Controller\SiteControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteControllerFactoryTest
 * @package SiteTest\Factory\Controller
 */
class SiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService('AuthorisationService', XMock::of(MotFrontendAuthorisationServiceInterface::class));
        $serviceManager->setService(MapperFactory::class, XMock::of(MapperFactory::class));
        $serviceManager->setService('MotIdentityProvider', XMock::of(MotIdentityProviderInterface::class));
        $serviceManager->setService('CatalogService', XMock::of(CatalogService::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteController::class, $factoryResult);
    }
}
