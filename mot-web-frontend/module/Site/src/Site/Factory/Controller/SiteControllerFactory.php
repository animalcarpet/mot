<?php

namespace Site\Factory\Controller;

use DvsaClient\MapperFactory;
use Site\Controller\SiteController;
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

        return new SiteController(
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(MapperFactory::class),
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get('CatalogService'),
            new Container(SiteController::SESSION_CNTR_KEY)
        );
    }
}
