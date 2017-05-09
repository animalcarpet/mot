<?php

namespace SiteApiTest\Service\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use DvsaEntities\Repository\SiteContactTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Factory\Service\SiteServiceFactory;
use SiteApi\Service\SiteService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\ServiceManager;
use DvsaEntities\Repository\SiteStatusRepository;

/**
 * Class SiteServiceFactoryTest.
 */
class SiteServiceFactoryTest extends AbstractServiceTestCase
{
    public function testSiteServiceFactoryReturnsSiteServiceInstance()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);
        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationServiceInterface::class));

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->mockMethod($identityProvider, 'getIdentity', null, new MotIdentity(1, 'unitTest'));
        $serviceManager->setService(MotIdentityProviderInterface::class, $identityProvider);

        $serviceManager->setService(SiteDetailsValidator::class, XMock::of(SiteDetailsValidator::class));
        $serviceManager->setService(TestingFacilitiesValidator::class, XMock::of(TestingFacilitiesValidator::class));
        $serviceManager->setService(ContactDetailsService::class, XMock::of(ContactDetailsService::class));
        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService(XssFilter::class, XMock::of(XssFilter::class));
        $serviceManager->setService(Hydrator::class, XMock::of(Hydrator::class));
        $serviceManager->setService(SiteValidator::class, XMock::of(SiteValidator::class));

        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(SiteTypeRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(SiteRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(SiteContactTypeRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(3), XMock::of(BrakeTestTypeRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(4), XMock::of(FacilityTypeRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(5), XMock::of(VehicleClassRepository::class));

        $mockAuthForTestAtSiteStatusRepo = XMock::of(AuthorisationForTestingMotAtSiteStatusRepository::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(6), $mockAuthForTestAtSiteStatusRepo);

        $mockSiteTestDailyScheduleRepo = XMock::of(SiteTestingDailyScheduleRepository::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(7), $mockSiteTestDailyScheduleRepo);

        $mockNonWorkDayCountryRepo = XMock::of(NonWorkingDayCountryRepository::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(8), $mockNonWorkDayCountryRepo);

        $siteStatusRepository = XMock::of(SiteStatusRepository::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(9), $siteStatusRepository);

        $factory = new SiteServiceFactory();

        // With the instances do we get the service
        $this->assertInstanceOf(
            SiteService::class,
            $factory->createService($serviceManager)
        );
    }
}
