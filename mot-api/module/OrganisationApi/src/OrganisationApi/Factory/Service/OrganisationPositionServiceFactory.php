<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Utility\Hydrator;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\OrganisationPositionHistory;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use OrganisationApi\Service\Mapper\OrganisationPositionMapper;
use OrganisationApi\Service\OrganisationPositionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Service\EventService;

class OrganisationPositionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new OrganisationPositionService(
            $entityManager->getRepository(Organisation::class),
            $entityManager->getRepository(OrganisationBusinessRoleMap::class),
            $entityManager->getRepository(OrganisationPositionHistory::class),
            new OrganisationPositionMapper($serviceLocator->get(Hydrator::class)),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $entityManager,
            $serviceLocator->get(EventService::class),
            $serviceLocator->get(PositionRemovalNotificationService::class),
            $serviceLocator->get(UserOrganisationNotificationService::class)
        );
    }
}
