<?php

namespace DvsaMotApiTest\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaMotApi\Factory\Service\MotTestServiceFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEntities\Repository\MotTestRepository;

class MotTestServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $entityManager = XMock::of(EntityManager::class);

        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(MotTestRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(MotTestValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(AuthorisationService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(ConfigurationRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(MotTestMapper::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(ReadMotTestAssertion::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(6), XMock::of(CreateMotTestService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(7), XMock::of(MysteryShopperHelper::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(8), XMock::of(TestingOutsideOpeningHoursNotificationService::class));

        $this->assertInstanceOf(
            MotTestService::class,
            (new MotTestServiceFactory())->createService($mockServiceLocator)
        );
    }
}
