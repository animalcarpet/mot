<?php

namespace PersonApi\Factory\Controller;

use DvsaAuthorisation\Service\UserRoleService;
use PersonApi\Controller\PersonPendingRoleController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonPendingRoleControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return PersonPendingRoleController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        /** @var UserRoleService $userRoleService */
        $userRoleService = $serviceLocator->get('UserRoleService');

        return new PersonPendingRoleController($userRoleService);
    }
}
