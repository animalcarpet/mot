<?php

namespace Application\Factory\Controller;

use Application\Controller\ReportController;
use Zend\Session\Container;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Service\LazyMotFrontendAuthorisationService;

class ReportControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var LazyMotFrontendAuthorisationService $authService */
        $authService = $serviceLocator->get('AuthorisationService');

        return new ReportController($authService);
    }
}