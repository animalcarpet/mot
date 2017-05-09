<?php

namespace TestSupport\Factory;

use TestSupport\Service\OneHundredMotTestsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;

class TestSupportOneHundredMotTestsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new OneHundredMotTestsService($serviceLocator->get(EntityManager::class));

        return $service;
    }
}
