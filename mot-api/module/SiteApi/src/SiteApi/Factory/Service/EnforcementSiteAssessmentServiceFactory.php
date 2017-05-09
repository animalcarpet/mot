<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\EnforcementSiteAssessmentService;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteRiskAssessmentServiceFactory.
 */
class EnforcementSiteAssessmentServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new EnforcementSiteAssessmentService(
            $entityManager,
            $serviceLocator->get(EnforcementSiteAssessmentValidator::class),
            $serviceLocator->get('config'),
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(XssFilter::class)
        );
    }
}
