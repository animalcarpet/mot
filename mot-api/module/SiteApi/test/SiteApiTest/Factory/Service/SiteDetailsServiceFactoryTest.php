<?php

namespace SiteApiTest\Service\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Factory\Service\SiteDetailsServiceFactory;
use SiteApi\Service\SiteDetailsService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\ServiceManager;

class SiteDetailsServiceFactoryTest extends AbstractServiceTestCase
{
    public function testSiteDetailsServiceFactoryReturnsService()
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
        $serviceManager->setService(XssFilter::class, XMock::of(XssFilter::class));
        $serviceManager->setService(EventService::class, XMock::of(EventService::class));

        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(SiteRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(VehicleClassRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(AuthorisationForTestingMotAtSiteStatusRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(3), XMock::of(SiteStatusRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(4), XMock::of(SiteTypeRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(5), XMock::of(NonWorkingDayCountryRepository::class));

        $factory = new SiteDetailsServiceFactory();

        $this->assertInstanceOf(
            SiteDetailsService::class,
            $factory->createService($serviceManager)
        );
    }
}
