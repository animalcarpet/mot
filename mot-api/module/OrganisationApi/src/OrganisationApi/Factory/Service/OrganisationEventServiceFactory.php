<?php

namespace OrganisationApi\Factory\Service;

use DvsaEntities\Entity\Organisation;
use OrganisationApi\Service\OrganisationEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Service\EventService;
use Doctrine\ORM\EntityManager;

class OrganisationEventServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrganisationEventService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        /** @var EntityManager $em */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new OrganisationEventService(
            $serviceLocator->get(EventService::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $entityManager,
            $entityManager->getRepository(Organisation::class)
        );
    }
}
