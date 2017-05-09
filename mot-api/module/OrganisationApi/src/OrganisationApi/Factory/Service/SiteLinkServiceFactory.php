<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\OrganisationSiteStatus;
use DvsaEntities\Entity\Site;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\NotificationService;
use OrganisationApi\Service\Mapper\OrganisationSiteLinkMapper;
use OrganisationApi\Service\SiteLinkService;
use OrganisationApi\Service\Validator\SiteLinkValidator;
use SiteApi\Service\MotTestInProgressService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteServiceFactory.
 */
class SiteLinkServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em = $serviceLocator->get(EntityManager::class);

        return new SiteLinkService(
            $em,
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get(NotificationService::class),
            $serviceLocator->get(MotTestInProgressService::class),
            $em->getRepository(Organisation::class),
            $em->getRepository(Site::class),
            $em->getRepository(OrganisationSiteMap::class),
            $em->getRepository(OrganisationSiteStatus::class),
            new OrganisationSiteLinkMapper(),
            new SiteLinkValidator(),
            new DateTimeHolder()
        );
    }
}
