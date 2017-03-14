<?php

namespace Dashboard\Factory\Security;

use Dashboard\Security\DashboardGuard;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DashboardGuardFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DashboardGuard(
            $serviceLocator->get(MotAuthorisationServiceInterface::class)
        );
    }
}
