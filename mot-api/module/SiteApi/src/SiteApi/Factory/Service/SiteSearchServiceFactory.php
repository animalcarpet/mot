<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Site;
use SiteApi\Service\Mapper\VtsMapper;
use SiteApi\Service\SiteSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteSearchServiceFactory.
 */
class SiteSearchServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new SiteSearchService(
            $entityManager,
            $entityManager->getRepository(Site::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            new VtsMapper()
        );
    }
}
