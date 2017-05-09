<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\GVTSTesterService;
use TestSupport\Service\AccountDataService;

class GVTSTesterServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new GVTSTesterService($accountService);

        return $service;
    }
}
