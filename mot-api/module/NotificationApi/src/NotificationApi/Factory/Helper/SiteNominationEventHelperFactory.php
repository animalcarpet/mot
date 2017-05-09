<?php

namespace NotificationApi\Factory\Helper;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Site;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NotificationApi\Service\Helper\SiteNominationEventHelper;
use DvsaEventApi\Service\EventService;

class SiteNominationEventHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new SiteNominationEventHelper(
            $serviceLocator->get(EventService::class),
            $entityManager->getRepository(EventSiteMap::class),
            $entityManager->getRepository(Site::class),
            new DateTimeHolder()
        );
    }
}
