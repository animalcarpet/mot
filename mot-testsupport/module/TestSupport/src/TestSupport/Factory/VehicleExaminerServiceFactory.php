<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\VehicleExaminerService;
use TestSupport\Service\AccountDataService;

class VehicleExaminerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountService = $serviceLocator->get(AccountDataService::class);
        $service = new VehicleExaminerService($accountService);

        return $service;
    }
}
