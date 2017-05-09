<?php

namespace DvsaMotApiTest\Factory\Service;

use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Factory\Service\TesterMotTestLogServiceFactory;
use DvsaMotApi\Service\TesterMotTestLogService;
use Zend\ServiceManager\ServiceManager;

class TesterMotTestLogServiceFactoryTest extends AbstractServiceTestCase
{
    /**
     * @var TesterMotTestLogService
     */
    private $factory;

    /** @var ServiceManager */
    private $serviceLocator;

    public function setUp()
    {
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('DvsaAuthorisationService', $this->getMockAuthorizationService());

        $mock = XMock::of(\Doctrine\ORM\EntityManager::class, ['getRepository']);

        $this->mockMethod($mock, 'getRepository', $this->any(), XMock::of(MotTestRepository::class));

        $this->serviceLocator->setService(\Doctrine\ORM\EntityManager::class, $mock);
    }

    public function testService()
    {
        $this->factory = new TesterMotTestLogServiceFactory();

        $this->assertInstanceOf(
            TesterMotTestLogService::class,
            $this->factory->createService($this->serviceLocator)
        );
    }
}
