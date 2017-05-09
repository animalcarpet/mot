<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\VehicleClass;
use NotificationApi\Service\NotificationService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\Validator\PersonalAuthorisationForMotTestingValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Service\EventService;
use PersonApi\Service\PersonService;

class PersonalAuthorisationForMotTestingServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new PersonalAuthorisationForMotTestingService(
            $entityManager,
            $serviceLocator->get(NotificationService::class),
            new PersonalAuthorisationForMotTestingValidator(),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get(PersonService::class),
            $entityManager->getRepository(AuthorisationForTestingMotStatus::class),
            $entityManager->getRepository(VehicleClass::class),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
