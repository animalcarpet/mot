<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\FinanceUserService;
use TestSupport\Service\AccountDataService;

class FinanceUserServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new FinanceUserService($accountService);

        return $service;
    }
}
