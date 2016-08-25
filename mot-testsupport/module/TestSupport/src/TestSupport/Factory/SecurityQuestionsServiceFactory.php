<?php

namespace TestSupport\Factory;

use Doctrine\ORM\EntityManager;
use TestSupport\Service\SecurityQuestionsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SecurityQuestionsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        return new SecurityQuestionsService($entityManager);
    }
}