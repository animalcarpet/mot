<?php

namespace Organisation\Factory\Controller;

use DvsaClient\MapperFactory;
use Organisation\Controller\AuthorisedExaminerController;
use SlotPurchase\Service\DirectDebitService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Class AuthorisedExaminerControllerFactory.
 */
class AuthorisedExaminerControllerFactory implements FactoryInterface
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

        return new AuthorisedExaminerController(
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(MapperFactory::class),
            $serviceLocator->get('MotIdentityProvider'),
            new Container(AuthorisedExaminerController::SESSION_CNTR_KEY),
            $serviceLocator->get(DirectDebitService::class)
        );
    }
}
