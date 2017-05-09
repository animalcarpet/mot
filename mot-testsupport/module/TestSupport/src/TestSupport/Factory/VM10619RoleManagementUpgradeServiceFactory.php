<?php

namespace TestSupport\Factory;

use TestSupport\Service\VM10619RoleManagementUpgradeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\AccountDataService;

class VM10619RoleManagementUpgradeServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new VM10619RoleManagementUpgradeService($accountService);

        return $service;
    }
}
