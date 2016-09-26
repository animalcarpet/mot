<?php

namespace DvsaMotApi\Factory\Controller;

use DvsaAuthentication\Login\LoginService;
use DvsaMotApi\Controller\SessionConfirmationController;
use Zend\Http\Response;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SessionConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return SessionConfirmationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();

        $response = $sm->get('Response');
        $loginService = $sm->get(LoginService::class);

        return new SessionConfirmationController($response, $loginService);
    }
}
