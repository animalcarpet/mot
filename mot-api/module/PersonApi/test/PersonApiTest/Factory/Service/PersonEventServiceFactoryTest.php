<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEventApi\Service\EventService;
use PersonApi\Factory\Service\PersonEventServiceFactory;
use PersonApi\Service\PersonEventService;
use Zend\ServiceManager\ServiceManager;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Entity\Person;
use DvsaAuthorisation\Service\AuthorisationService;

/**
 * Class PersonEventServiceFactoryTest.
 *
 * @group event
 */
class PersonEventServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with(Person::class)
            ->willReturn(XMock::of(PersonRepository::class));

        $serviceManager->setService(EntityManager::class, $entityManager);
        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationService::class));

        // Create the factory
        $factory = new PersonEventServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(PersonEventService::class, $factoryResult);
    }
}
