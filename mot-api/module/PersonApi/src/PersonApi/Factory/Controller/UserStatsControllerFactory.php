<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\UserStatsController;
use PersonApi\Service\UserStatsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class UserStatsControllerFactory.
 *
 * Generates the UserStatsController, injecting dependencies
 */
class UserStatsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return UserStatsController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var UserStatsService $userStatsService */
        $userStatsService = $serviceLocator->get(UserStatsService::class);

        return new UserStatsController($userStatsService);
    }
}
