<?php

namespace SiteTest\Factory\Controller;

use Application\Service\CatalogService;
use Core\Catalog\BusinessRole\BusinessRoleCatalog;
use Core\Catalog\EnumCatalog;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Site\Action\SiteTestQualityAction;
use Site\Action\UserTestQualityAction;
use Site\Controller\SiteController;
use Site\Factory\Controller\SiteControllerFactory;
use Zend\ServiceManager\ServiceManager;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsAtSiteBreadcrumbs;

/**
 * Class SiteControllerFactoryTest.
 */
class SiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $enumCatalog = XMock::of(EnumCatalog::class);
        $enumCatalog->expects($this->any())->method('businessRole')->willReturn(XMock::of(BusinessRoleCatalog::class));

        $serviceManager = new ServiceManager();

        $serviceManager->setService('AuthorisationService', XMock::of(MotFrontendAuthorisationServiceInterface::class));
        $serviceManager->setService(MapperFactory::class, XMock::of(MapperFactory::class));
        $serviceManager->setService('MotIdentityProvider', XMock::of(MotIdentityProviderInterface::class));
        $serviceManager->setService('CatalogService', XMock::of(CatalogService::class));
        $serviceManager->setService(EnumCatalog::class, $enumCatalog);
        $serviceManager->setService(SiteTestQualityAction::class, XMock::of(SiteTestQualityAction::class));
        $serviceManager->setService(ViewVtsTestQualityAssertion::class, XMock::of(ViewVtsTestQualityAssertion::class));
        $serviceManager->setService(UserTestQualityAction::class, Xmock::of(UserTestQualityAction::class));
        $serviceManager->setService(ContextProvider::class, Xmock::of(ContextProvider::class));
        $serviceManager->setService(TesterTqiComponentsAtSiteBreadcrumbs::class, Xmock::of(TesterTqiComponentsAtSiteBreadcrumbs::class));

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteController::class, $factoryResult);
    }
}
