<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use PersonApi\Factory\Service\UserStatsServiceFactory;
use PersonApi\Service\UserStatsService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class UserStatsServiceFactoryTest.
 */
class UserStatsServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod(
            $entityManager,
            'getRepository',
            $this->at(0),
            XMock::of(
                MotTestRepository::class
            )
        );

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);

        $this->assertInstanceOf(
            UserStatsService::class,
            (new UserStatsServiceFactory())->createService($mockServiceLocator)
        );
    }
}
