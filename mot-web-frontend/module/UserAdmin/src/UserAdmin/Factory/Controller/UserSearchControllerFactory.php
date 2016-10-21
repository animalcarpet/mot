<?php

namespace UserAdmin\Factory\Controller;

use UserAdmin\Controller\UserSearchController;
use UserAdmin\Service\DateOfBirthFilterService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * factory to create UserSearchController instances
 */
class UserSearchControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserSearchController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        /** @var DateOfBirthFilterService $dateOfBirthFilterService */
        $dateOfBirthFilterService = $serviceLocator->get(DateOfBirthFilterService::class);

        /** @var UserSearchController $controller */
        $controller = new UserSearchController($dateOfBirthFilterService);

        return $controller;
    }
}