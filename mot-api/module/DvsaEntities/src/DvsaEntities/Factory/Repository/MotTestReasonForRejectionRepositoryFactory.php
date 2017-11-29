<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Repository\MotTestReasonForRejectionRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestReasonForRejectionRepositoryFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MotTestReasonForRejectionRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return $entityManager->getRepository(MotTestReasonForRejection::class);
    }
}