<?php

namespace OrganisationApi\Factory\Controller;

use OrganisationApi\Controller\AuthorisedExaminerNameController;
use OrganisationApi\Service\AuthorisedExaminerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthorisedExaminerNameControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new AuthorisedExaminerNameController(
            $serviceLocator->get(AuthorisedExaminerService::class)
        );
    }
}
