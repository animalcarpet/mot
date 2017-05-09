<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEventApi\Service\EventService;
use PersonApi\Service\PersonEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Entity\Person;

class PersonEventServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonEventService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        /** @var EntityManager $em */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new PersonEventService(
            $serviceLocator->get(EventService::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $entityManager,
            $entityManager->getRepository(Person::class)
        );
    }
}
