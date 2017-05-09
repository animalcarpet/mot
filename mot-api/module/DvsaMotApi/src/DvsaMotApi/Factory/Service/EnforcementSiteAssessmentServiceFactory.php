<?php

namespace DvsaMotApi\Factory\Service;

use DataCatalogApi\Service\DataCatalogService;
use Doctrine\ORM\EntityManager;
use DvsaMotApi\Service\EnforcementSiteAssessmentService;
use DvsaMotApi\Service\UserService;
use OrganisationApi\Service\AuthorisedExaminerService;
use SiteApi\Service\SiteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EnforcementSiteAssessmentServiceFactory.
 */
class EnforcementSiteAssessmentServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EnforcementSiteAssessmentService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get('Hydrator'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('TesterService'),
            $serviceLocator->get(SiteService::class),
            $serviceLocator->get(AuthorisedExaminerService::class),
            $serviceLocator->get(DataCatalogService::class),
            $serviceLocator->get(UserService::class)
        );
    }
}
