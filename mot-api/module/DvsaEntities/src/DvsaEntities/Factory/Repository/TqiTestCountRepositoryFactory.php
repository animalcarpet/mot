<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\TqiTestCount;
use DvsaEntities\Repository\TqiTestCountRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TqiTestCountRepositoryFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TqiTestCountRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return $entityManager->getRepository(TqiTestCount::class);
    }
}