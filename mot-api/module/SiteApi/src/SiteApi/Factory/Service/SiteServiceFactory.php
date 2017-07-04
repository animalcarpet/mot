<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\SiteRiskAssessmentRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\Mapper\SiteBusinessRoleMapMapper;
use SiteApi\Service\SiteService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Entity\SiteStatus;

/**
 * Class SiteServiceFactory.
 */
class SiteServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        /** @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');
        /** @var Hydrator $hydrator */
        $hydrator = $serviceLocator->get(Hydrator::class);
        $updateVtsAssertion = new UpdateVtsAssertion($authorisationService);

        return new SiteService(
            $entityManager,
            $authorisationService,
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $serviceLocator->get(ContactDetailsService::class),
            $serviceLocator->get(EventService::class),
            $entityManager->getRepository(EnforcementSiteAssessment::class),
            $entityManager->getRepository(SiteType::class),
            $entityManager->getRepository(Site::class),
            $entityManager->getRepository(SiteContactType::class),
            $entityManager->getRepository(BrakeTestType::class),
            $entityManager->getRepository(FacilityType::class),
            $entityManager->getRepository(VehicleClass::class),
            $entityManager->getRepository(AuthorisationForTestingMotAtSiteStatus::class),
            $entityManager->getRepository(SiteTestingDailySchedule::class),
            $entityManager->getRepository(NonWorkingDayCountry::class),
            $entityManager->getRepository(SiteStatus::class),
            $serviceLocator->get(XssFilter::class),
            new SiteBusinessRoleMapMapper($hydrator),
            $updateVtsAssertion,
            $hydrator,
            new SiteValidator(
                null,
                $serviceLocator->get(TestingFacilitiesValidator::class),
                $serviceLocator->get(SiteDetailsValidator::class)
            )
        );
    }
}
