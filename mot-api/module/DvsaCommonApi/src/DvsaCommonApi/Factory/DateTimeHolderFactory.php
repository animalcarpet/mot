<?php

namespace DvsaCommonApi\Factory;

use DvsaCommon\Date\DateTimeHolder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DateTimeHolderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DateTimeHolder();
    }
}
