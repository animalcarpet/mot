<?php

namespace OrganisationApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Factory\Service\OrganisationEventServiceFactory;
use OrganisationApi\Service\OrganisationEventService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class OrganisationEventServiceFactoryTest.
 */
class OrganisationEventServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with(Organisation::class)
            ->willReturn(XMock::of(OrganisationRepository::class));

        $serviceManager->setService(EntityManager::class, $entityManager);
        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationService::class));

        // Create the factory
        $factory = new  OrganisationEventServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(OrganisationEventService::class, $factoryResult);
    }
}
