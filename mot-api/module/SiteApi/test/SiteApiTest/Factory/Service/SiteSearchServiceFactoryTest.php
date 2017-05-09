<?php

namespace SiteApiTest\Service\Factory;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\SiteRepository;
use SiteApi\Factory\Service\SiteSearchServiceFactory;
use SiteApi\Service\SiteSearchService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteSearchServiceFactoryTest.
 */
class SiteSearchServiceFactoryTest extends AbstractServiceTestCase
{
    public function testEventServiceGetList()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $serviceManager->setService(EntityManager::class, $entityManager);

        $repository = XMock::of(SiteRepository::class);
        $this->mockMethod($entityManager, 'getRepository', $this->once(), $repository);

        $auth = XMock::of(MotAuthorisationServiceInterface::class);
        $serviceManager->setService('DvsaAuthorisationService', $auth);

        $factory = new SiteSearchServiceFactory();

        $this->assertInstanceOf(
            SiteSearchService::class,
            $factory->createService($serviceManager)
        );
    }
}
