<?php

namespace SiteApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Factory\Service\SiteTestingFacilitiesServiceFactory;
use SiteApi\Service\SiteTestingFacilitiesService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;
use Zend\ServiceManager\ServiceManager;

class SiteTestingFacilitiesServiceFactoryTest extends AbstractServiceTestCase
{
    public function testSiteTestingFacilitiesServiceFactoryReturnsService()
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
        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService(XssFilter::class, XMock::of(XssFilter::class));

        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(SiteRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(FacilityTypeRepository::class));

        $factory = new SiteTestingFacilitiesServiceFactory();

        $this->assertInstanceOf(
            SiteTestingFacilitiesService::class,
            $factory->createService($serviceManager)
        );
    }
}
