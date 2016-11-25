<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use PersonApi\Factory\Service\DashboardServiceFactory;
use PersonApi\Factory\Service\UserStatsServiceFactory;
use PersonApi\Service\DashboardService;
use PersonApi\Service\UserStatsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class UserStatsServiceFactoryTest
 *
 * @package PersonApiTest\Factory\Service
 */
class UserStatsServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testCreateServiceReturnsService()
    {
        $entityManager = XMock::of(EntityManager::class);
        $mysteryShopperHelper = XMock::of(MysteryShopperHelper::class);
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
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), $mysteryShopperHelper);

        $this->assertInstanceOf(
            UserStatsService::class,
            (new UserStatsServiceFactory())->createService($mockServiceLocator)
        );
    }
}
