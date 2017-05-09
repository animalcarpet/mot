<?php

namespace NotificationApi\Factory\Helper;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use DvsaEventApi\Service\EventService;

class OrganisationNominationEventHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new OrganisationNominationEventHelper(
            $serviceLocator->get(EventService::class),
            $entityManager->getRepository(EventOrganisationMap::class),
            $entityManager->getRepository(AuthorisationForAuthorisedExaminer::class),
            new DateTimeHolder()
        );
    }
}
