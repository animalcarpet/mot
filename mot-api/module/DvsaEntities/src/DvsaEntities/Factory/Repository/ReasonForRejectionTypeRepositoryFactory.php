<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Repository\ReasonForRejectionTypeRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReasonForRejectionTypeRepositoryFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ReasonForRejectionTypeRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return $entityManager->getRepository(ReasonForRejectionType::class);
    }
}