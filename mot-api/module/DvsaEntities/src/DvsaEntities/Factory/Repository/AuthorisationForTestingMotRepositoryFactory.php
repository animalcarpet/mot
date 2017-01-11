<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthorisationForTestingMotRepositoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return $entityManager->getRepository(AuthorisationForTestingMot::class);
    }
}
