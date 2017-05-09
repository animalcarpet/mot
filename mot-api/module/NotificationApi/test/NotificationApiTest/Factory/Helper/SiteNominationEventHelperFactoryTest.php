<?php

namespace DvsaMotApiTest\Factory\Service;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use NotificationApi\Service\Helper\SiteNominationEventHelper;
use NotificationApi\Factory\Helper\SiteNominationEventHelperFactory;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Repository\EventSiteMapRepository;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\SiteRepository;
use Doctrine\ORM\EntityManager;

class SiteNominationEventHelperFactoryTest extends AbstractServiceTestCase
{
    /** @var ServiceManager */
    private $serviceLocator;

    public function setUp()
    {
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EventService::class, XMock::of(EventService::class));

        $em = XMock::of(EntityManager::class);
        $em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($entity) {
                switch ($entity) {
                    case EventSiteMap::class:
                        return XMock::of(EventSiteMapRepository::class);
                    case Site::class:
                        return XMock::of(SiteRepository::class);
                    default:
                        return null;
                }
            });

        $this->serviceLocator->setService(EntityManager::class, $em);
    }

    public function testService()
    {
        $factory = new SiteNominationEventHelperFactory();

        $this->assertInstanceOf(
            SiteNominationEventHelper::class,
            $factory->createService($this->serviceLocator)
        );
    }
}
