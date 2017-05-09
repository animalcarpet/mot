<?php

namespace NonWorkingDaysApi\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NonWorkingDaysApi\NonWorkingDaysHelper;

class NonWorkingDaysHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NonWorkingDaysHelper($serviceLocator->get('NonWorkingDaysLookupManager'));
    }
}
