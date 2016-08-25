<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Helper\SitePermissionsHelper;
use TestSupport\Helper\TestSupportRestClientHelper;
use Doctrine\ORM\EntityManager;

class SitePermissionsHelperFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SitePermissionsHelper(
            $serviceLocator->get(EntityManager::class)
        );
    }

}