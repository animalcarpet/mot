<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTestStatus;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestStatusRepositoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return $entityManager->getRepository(MotTestStatus::class);
    }
}