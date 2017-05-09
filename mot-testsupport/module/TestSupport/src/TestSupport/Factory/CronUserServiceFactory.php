<?php

namespace TestSupport\Factory;

use TestSupport\Service\CronUserService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\AccountDataService;

class CronUserServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new CronUserService($accountService);

        return $service;
    }
}
