<?php

namespace Application\Factory;

use Application\Controller\FormsController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use DvsaCommon\HttpRestJson\Client;

class FormsControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new FormsController(
            $serviceLocator->get('LoggedInUserManager'),
            $serviceLocator->get('AuthorisationService'),
            $serviceLocator->get(Client::class)
        );
    }
}
