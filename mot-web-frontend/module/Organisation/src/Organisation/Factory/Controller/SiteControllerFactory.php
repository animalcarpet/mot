<?php

namespace Organisation\Factory\Controller;

use DvsaClient\MapperFactory;
use Organisation\Controller\AuthorisedExaminerController;
use Organisation\Controller\SiteController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteControllerFactory.
 */
class SiteControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return AuthorisedExaminerController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new SiteController(
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
