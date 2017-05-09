<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Organisation;
use OrganisationApi\Service\Mapper\SiteMapper;
use OrganisationApi\Service\SiteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteServiceFactory.
 */
class SiteServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SiteService(
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(EntityManager::class)->getRepository(Organisation::class),
            new SiteMapper()
        );
    }
}
