<?php

namespace SiteApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\EnforcementSiteAssessmentService;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use Zend\ServiceManager\ServiceManager;
use SiteApi\Factory\Service\EnforcementSiteAssessmentServiceFactory;

class EnforcementSiteAssessmentServiceFactoryTest extends AbstractServiceTestCase
{
    public function testSiteTestingFacilitiesServiceFactoryReturnsService()
    {
        $config['site_assessment']['green'] = '100';
        $config['site_assessment']['amber'] = '100';
        $config['site_assessment']['red'] = '100';

        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);
        $serviceManager->setService(EnforcementSiteAssessmentValidator::class, XMock::of(EnforcementSiteAssessmentValidator::class));
        $serviceManager->setService('Config', $config);

        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationServiceInterface::class));
        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->mockMethod($identityProvider, 'getIdentity', null, new MotIdentity(1, 'unitTest'));
        $serviceManager->setService(MotIdentityProviderInterface::class, $identityProvider);

        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService(XssFilter::class, XMock::of(XssFilter::class));

        $factory = new EnforcementSiteAssessmentServiceFactory();

        $this->assertInstanceOf(
            EnforcementSiteAssessmentService::class,
            $factory->createService($serviceManager)
        );
    }
}
