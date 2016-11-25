<?php

namespace DvsaMotApiTest\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Factory\Service\VehicleHistoryServiceFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\VehicleHistoryService;
use Zend\ServiceManager\ServiceLocatorInterface;

class VehicleHistoryServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(PersonRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(MotTestRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(ConfigurationRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(MysteryShopperHelper::class));

        $this->assertInstanceOf(
            VehicleHistoryService::class,
            (new VehicleHistoryServiceFactory())->createService($mockServiceLocator)
        );
    }
}
