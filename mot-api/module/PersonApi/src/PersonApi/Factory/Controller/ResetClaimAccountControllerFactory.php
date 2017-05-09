<?php

namespace PersonApi\Factory\Controller;

use Doctrine\ORM\EntityManager;
use PersonApi\Controller\ResetClaimAccountController;
use UserApi\HelpDesk\Service\ResetClaimAccountService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ResetClaimAccountControllerFactory.
 */
class ResetClaimAccountControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return ResetClaimAccountController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        /** @var ResetClaimAccountService $resetClaimAccountService */
        $resetClaimAccountService = $serviceLocator->get(ResetClaimAccountService::class);

        return new ResetClaimAccountController(
            $entityManager,
            $resetClaimAccountService
        );
    }
}
