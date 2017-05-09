<?php

namespace TestSupport\Factory;

use TestSupport\Service\AccountDataService;
use TestSupport\Service\CatUserService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CatUserServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new CatUserService($accountService);

        return $service;
    }
}
