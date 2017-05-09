<?php

namespace SiteApi\Factory\Service;

use SiteApi\Service\SiteEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\Site;
use Doctrine\ORM\EntityManager;

class SiteEventServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SiteEventService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $em */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new SiteEventService(
            $serviceLocator->get(EventService::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $entityManager,
            $entityManager->getRepository(Site::class)
        );
    }
}
