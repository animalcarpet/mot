<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonRoleController;
use PersonApi\Service\PersonRoleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonRoleControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonRoleController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var PersonRoleService $personRoleService */
        $personRoleService = $serviceLocator->get(PersonRoleService::class);

        return new PersonRoleController($personRoleService);
    }
}
