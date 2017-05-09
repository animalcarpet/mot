<?php

namespace TestSupport\Factory;

use Doctrine\ORM\EntityManager;
use TestSupport\Service\DocumentService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DocumentServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        $service = new DocumentService($entityManager);

        return $service;
    }
}
