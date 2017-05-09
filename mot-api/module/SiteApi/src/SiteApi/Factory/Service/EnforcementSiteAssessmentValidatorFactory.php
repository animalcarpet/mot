<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Configuration\MotConfig;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnforcementSiteAssessmentValidatorFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new EnforcementSiteAssessmentValidator(
            $config,
            $entityManager
        );
    }
}
