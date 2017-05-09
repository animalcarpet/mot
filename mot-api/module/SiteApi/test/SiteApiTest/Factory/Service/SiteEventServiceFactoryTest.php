<?php

namespace SiteApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Factory\Service\SiteEventServiceFactory;
use SiteApi\Service\SiteEventService;
use Zend\ServiceManager\ServiceManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;

/**
 * @throws \Exception
 * @group event
 */
class SiteEventServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Site::class)
            ->willReturn(XMock::of(SiteRepository::class));

        $serviceManager->setService(EventService::class, XMock::of(EventService::class));
        $serviceManager->setService('DvsaAuthorisationService', XMock::of(AuthorisationServiceInterface::class));
        $serviceManager->setService(EntityManager::class, $entityManager);

        // Create the factory
        $factory = new SiteEventServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(SiteEventService::class, $factoryResult);
    }
}
