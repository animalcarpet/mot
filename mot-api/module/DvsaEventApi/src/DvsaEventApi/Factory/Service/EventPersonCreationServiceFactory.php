<?php

namespace DvsaEventApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaEventApi\Service\EventPersonCreationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventPersonCreationServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \DvsaEventApi\Service\EventService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new EventPersonCreationService(
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(EventType::class),
            $entityManager
        );
    }
}
