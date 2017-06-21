<?php

namespace DvsaEntityTest\Factory\Repository;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaEntities\Cache\Repository\CachedRbacRepository;
use DvsaEntities\Factory\Repository\RbacRepositoryFactory;
use DvsaEntities\Repository\RbacRepository;
use DvsaEntities\Repository\SqlRbacRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RbacRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $services;

    protected function setUp()
    {
        $this->services = [
            EntityManager::class => $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock(),
            Cache::class => $this->getMockBuilder(Cache::class)->disableOriginalConstructor()->getMock(),
            'tokenService' => $this->getMockBuilder(TokenServiceInterface::class)->disableOriginalConstructor()->getMock(),
            'config' => []
        ];
    }

    public function testItIsAZendServiceManagerFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, new RbacRepositoryFactory());
    }

    public function testItCreatesTheRbacRepository()
    {
        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new RbacRepositoryFactory();

        $this->assertInstanceOf(RbacRepository::class, $factory->createService($serviceLocator));
    }

    public function testItCreatesSqlRbacRepositoryByDefault()
    {
        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new RbacRepositoryFactory();

        $this->assertInstanceOf(SqlRbacRepository::class, $factory->createService($serviceLocator));
    }

    public function testItCreatesCachedRbacRepositoryIfEnabled()
    {
        $this->services['config'] = ['cache' => ['rbac_repository' => ['enabled' => true]]];
        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new RbacRepositoryFactory();

        $this->assertInstanceOf(CachedRbacRepository::class, $factory->createService($serviceLocator));
    }

    public function testItCreatesSqlRbacRepositoryIfCacheIsDisabled()
    {
        $this->services['config'] = ['cache' => ['rbac_repository' => ['enabled' => false]]];
        $serviceLocator = $this->getServiceLocator($this->services);

        $factory = new RbacRepositoryFactory();

        $this->assertInstanceOf(SqlRbacRepository::class, $factory->createService($serviceLocator));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceLocator(array $services)
    {
        $serviceLocator = $this->getMockBuilder(ServiceLocatorInterface::class)->disableOriginalConstructor()->getMock();

        $serviceLocator->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function ($serviceName) use ($services) {
                return $services[$serviceName];
            }));

        return $serviceLocator;
    }
}
