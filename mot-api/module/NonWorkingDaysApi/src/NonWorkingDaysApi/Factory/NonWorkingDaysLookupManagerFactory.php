<?php

namespace NonWorkingDaysApi\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NonWorkingDaysApi\NonWorkingDaysLookupManager;

class NonWorkingDaysLookupManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NonWorkingDaysLookupManager($serviceLocator->get('NonWorkingDaysProvider'));
    }
}
