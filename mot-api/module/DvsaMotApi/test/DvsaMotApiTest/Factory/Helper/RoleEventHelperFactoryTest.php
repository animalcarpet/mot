<?php

namespace DvsaMotApiTest\Factory\Helper;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaMotApi\Factory\Helper\RoleEventHelperFactory;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Repository\EventPersonMapRepository;

class RoleEventHelperFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $identity = XMock::of(MotIdentityProviderInterface::class);
        $eventService = XMock::of(EventService::class);
        $entityManager = XMock::of(EntityManager::class);
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(EventPersonMap::class)
            ->willReturn(XMock::of(EventPersonMapRepository::class));

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(MotIdentityProviderInterface::class, $identity);
        $this->serviceLocator->setService(EventService::class, $eventService);
        $this->serviceLocator->setService(EntityManager::class, $entityManager);
    }

    public function testReplacementCertificateServiceFactory()
    {
        $service = (new RoleEventHelperFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            RoleEventHelper::class,
            $service
        );
    }
}
