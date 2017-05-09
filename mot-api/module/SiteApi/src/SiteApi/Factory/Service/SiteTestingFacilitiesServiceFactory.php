<?php

namespace SiteApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Site;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\SiteTestingFacilitiesService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteTestingFacilitiesServiceFactory implements FactoryInterface
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

        /** @var FacilityTypeRepository $facilityTypeRepository */
        $facilityTypeRepository = $entityManager->getRepository(FacilityType::class);

        return new SiteTestingFacilitiesService(
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
            $facilityTypeRepository,
            $serviceLocator->get(MotIdentityProviderInterface::class)->getIdentity(),
            $entityManager
        );
    }
}
