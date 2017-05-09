<?php

namespace OrganisationApi\Factory\Controller;

use OrganisationApi\Controller\AuthorisedExaminerController;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\UpdateAeDetailsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthorisedExaminerControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new AuthorisedExaminerController(
            $serviceLocator->get(AuthorisedExaminerService::class),
            $serviceLocator->get(UpdateAeDetailsService::class)
        );
    }
}
