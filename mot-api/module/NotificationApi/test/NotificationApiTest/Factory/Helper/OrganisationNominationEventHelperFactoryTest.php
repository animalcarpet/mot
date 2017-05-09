<?php

namespace DvsaMotApiTest\Factory\Service;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;
use NotificationApi\Factory\Helper\OrganisationNominationEventHelperFactory;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Repository\EventOrganisationMapRepository;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use Doctrine\ORM\EntityManager;

class OrganisationNominationEventHelperFactoryTest extends AbstractServiceTestCase
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
                    case EventOrganisationMap::class:
                        return XMock::of(EventOrganisationMapRepository::class);
                    case AuthorisationForAuthorisedExaminer::class:
                        return XMock::of(AuthorisationForAuthorisedExaminerRepository::class);
                    default:
                        return null;
                }
            });

        $this->serviceLocator->setService(EntityManager::class, $em);
    }

    public function testService()
    {
        $factory = new OrganisationNominationEventHelperFactory();

        $this->assertInstanceOf(
            OrganisationNominationEventHelper::class,
            $factory->createService($this->serviceLocator)
        );
    }
}
