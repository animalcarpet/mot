<?php

namespace TestSupport\Factory;

use TestSupport\Service\AccountDataService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\AccountService;
use Doctrine\ORM\EntityManager;

class AccountDataServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountService::class);
        $entityManagerService = $serviceLocator->get(EntityManager::class);
        $service = new AccountDataService($accountService, $entityManagerService);

        return $service;
    }
}
