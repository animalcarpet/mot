<?php

namespace Site\Factory\Controller;

use Core\Catalog\EnumCatalog;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsAtSiteBreadcrumbs;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use Site\Action\SiteTestQualityAction;
use Site\Action\UserTestQualityAction;
use Site\Controller\SiteController;
use Site\Service\SiteBreadcrumbsBuilder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Class SiteControllerFactory.
 */
class SiteControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SiteController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var EnumCatalog $enumCatalog */
        $enumCatalog = $serviceLocator->get(EnumCatalog::class);

        return new SiteController(
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(MapperFactory::class),
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get('CatalogService'),
            new Container(SiteController::SESSION_CNTR_KEY),
            $enumCatalog->businessRole(),
            $serviceLocator->get(SiteTestQualityAction::class),
            $serviceLocator->get(UserTestQualityAction::class),
            $serviceLocator->get(ViewVtsTestQualityAssertion::class),
            $serviceLocator->get(ContextProvider::class),
            $serviceLocator->get(TesterTqiComponentsAtSiteBreadcrumbs::class),
            $serviceLocator->get(SiteBreadcrumbsBuilder::class)
        );
    }
}
