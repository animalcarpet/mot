<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaMotApi\Service\TesterService;
use NotificationApi\Service\NotificationService;
use PersonApi\Factory\Service\DashboardServiceFactory;
use PersonApi\Service\DashboardService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use SiteApi\Service\SiteService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DashboardServiceFactoryTest.
 */
class DashboardServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod(
            $entityManager,
            'getRepository',
            $this->at(0),
            XMock::of(
                AuthorisationForAuthorisedExaminerRepository::class
            )
        );

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(VehicleService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(ParamObfuscator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(SiteService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(SpecialNoticeService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(6), XMock::of(NotificationService::class));
        $this->mockMethod(
            $mockServiceLocator, 'get', $this->at(7), XMock::of(PersonalAuthorisationForMotTestingService::class)
        );
        $this->mockMethod($mockServiceLocator, 'get', $this->at(8), XMock::of(TesterService::class));

        $this->assertInstanceOf(
            DashboardService::class,
            (new DashboardServiceFactory())->createService($mockServiceLocator)
        );
    }
}
