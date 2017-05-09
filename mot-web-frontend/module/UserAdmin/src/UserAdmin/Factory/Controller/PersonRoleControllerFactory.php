<?php

namespace UserAdmin\Factory\Controller;

use UserAdmin\Controller\PersonRoleController;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for {@link \UserAdmin\Controller\PersonRoleController}.
 */
class PersonRoleControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $appServiceLocator = $controllerManager->getServiceLocator();

        $authorisationService = $appServiceLocator->get('AuthorisationService');
        $personRoleManagementService = $appServiceLocator->get(PersonRoleManagementService::class);

        $controller = new PersonRoleController(
            $authorisationService,
            $personRoleManagementService
        );

        return $controller;
    }
}
