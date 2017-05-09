<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaEntities\Entity\Site;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEntities\Entity\SiteStatus;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\SiteDetailsService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteDetailsServiceFactory implements FactoryInterface
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
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');
        $updateVtsAssertion = new UpdateVtsAssertion($authorisationService);

        /** @var XssFilter $xssFilter */
        $xssFilter = $serviceLocator->get(XssFilter::class);

        /** @var EventService $eventService */
        $eventService = $serviceLocator->get(EventService::class);

        /** @var SiteRepository $siteRepository */
        $siteRepository = $entityManager->getRepository(Site::class);

        /** @var VehicleClassRepository $vehicleClassRepository */
        $vehicleClassRepository = $entityManager->getRepository(VehicleClass::class);

        /** @var AuthorisationForTestingMotAtSiteStatusRepository $authForTestingMotStatusRepository */
        $authForTestingMotStatusRepository = $entityManager->getRepository(AuthorisationForTestingMotAtSiteStatus::class);

        /** @var SiteStatusRepository $siteStatusRepository */
        $siteStatusRepository = $entityManager->getRepository(SiteStatus::class);

        /** @var SiteDetailsValidator $siteDetailsValidator */
        $siteDetailsValidator = $serviceLocator->get(SiteDetailsValidator::class);

        /** @var SiteTypeRepository $siteTypeRepository */
        $siteTypeRepository = $entityManager->getRepository(SiteType::class);

        $nonWorkingDayCountryRepository = $entityManager->getRepository(NonWorkingDayCountry::class);

        return new SiteDetailsService(
            $siteRepository,
            $authorisationService,
            $updateVtsAssertion,
            $xssFilter,
            new SiteValidator(
                null,
                $serviceLocator->get(TestingFacilitiesValidator::class),
                $serviceLocator->get(SiteDetailsValidator::class)
            ),
            $eventService,
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $entityManager,
            $vehicleClassRepository,
            $authForTestingMotStatusRepository,
            $siteStatusRepository,
            $siteDetailsValidator,
            $siteTypeRepository,
            $nonWorkingDayCountryRepository
        );
    }
}
