<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Authentication\AuthenticationService;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\NotificationService;
use PersonApi\Factory\Service\PersonalAuthorisationForMotTestingServiceFactory;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class PersonalAuthorisationForMotTestingServiceFactoryTest.
 */
class PersonalAuthorisationForMotTestingServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod(
            $entityManager,
            'getRepository',
            $this->at(0),
            XMock::of(AuthorisationForTestingMotStatusRepository::class)
        );
        $this->mockMethod(
            $entityManager,
            'getRepository',
            $this->at(1),
            XMock::of(VehicleClassRepository::class)
        );

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(NotificationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(EventService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(PersonService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(AuthenticationService::class));

        $this->assertInstanceOf(
            PersonalAuthorisationForMotTestingService::class,
            (new PersonalAuthorisationForMotTestingServiceFactory())->createService($mockServiceLocator)
        );
    }
}
