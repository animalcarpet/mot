<?php

namespace TestSupport\Factory;

use TestSupport\Service\DVLAOperativeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\AccountDataService;

class DVLAOperativeServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new DVLAOperativeService($accountService);

        return $service;
    }
}
