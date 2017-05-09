<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Service\AEService;
use Doctrine\ORM\EntityManager;
use TestSupport\Helper\TestSupportRestClientHelper;

class AEServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new AEService(
            $serviceLocator->get(TestSupportRestClientHelper::class),
            $serviceLocator->get(EntityManager::class)
        );

        return $service;
    }
}
