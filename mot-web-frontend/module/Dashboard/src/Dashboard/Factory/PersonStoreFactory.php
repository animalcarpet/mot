<?php

namespace Dashboard\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dashboard\PersonStore;
use Application\Data\ApiPersonalDetails;

class PersonStoreFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PersonStore(
            $serviceLocator->get(ApiPersonalDetails::class)
        );
    }
}
