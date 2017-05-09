<?php

namespace TestSupport\Factory;

use Doctrine\ORM\EntityManager;
use TestSupport\Service\ClaimAccountService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ClaimAccountServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        $service = new ClaimAccountService($entityManager);

        return $service;
    }
}
