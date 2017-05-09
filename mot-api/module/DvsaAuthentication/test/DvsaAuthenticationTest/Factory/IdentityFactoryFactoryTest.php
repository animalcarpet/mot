<?php

namespace DvsaAuthenticationTest\Factory;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Factory\IdentityFactoryFactory;
use DvsaAuthentication\IdentityFactory\CacheableIdentityFactory;
use DvsaAuthentication\IdentityFactory\DoctrineIdentityFactory;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class IdentityFactoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityFactoryFactory
     */
    private $identityFactoryFactory;

    private $entityManager;

    private $cache;

    private $featureToggles;

    protected function setUp()
    {
        $this->identityFactoryFactory = new IdentityFactoryFactory();

        $personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->featureToggles = $this->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager->expects($this->any())
            ->method('getRepository')
            ->with(Person::class)
            ->willReturn($personRepository);

        $this->cache = $this->getMock(Cache::class);
    }

    public function testItIsAZendFactory()
    {
        $this->assertInstanceOf(FactoryInterface::class, $this->identityFactoryFactory);
    }

    public function testItCreatesDoctrineIdentityFactoryByDefault()
    {
        $serviceLocator = $this->getServiceLocator([
            'config' => [],
            EntityManager::class => $this->entityManager,
            Cache::class => $this->cache,
            'Feature\FeatureToggles' => $this->featureToggles,
        ]);

        $identityFactory = $this->identityFactoryFactory->createService($serviceLocator);

        $serviceLocator = new ServiceManager();
        $serviceLocator->setAllowOverride(true);

        $this->assertInstanceOf(DoctrineIdentityFactory::class, $identityFactory);
    }

    public function testItCreatesCacheableIdentityFactoryIfConfigured()
    {
        $serviceLocator = $this->getServiceLocator([
            'config' => [
                'cache' => [
                    'identity_factory' => [
                        'enabled' => true,
                        'options' => [
                            'ttl' => 300,
                        ],
                    ],
                ],
            ],
            EntityManager::class => $this->entityManager,
            Cache::class => $this->cache,
            'Feature\FeatureToggles' => $this->featureToggles,
        ]);

        $identityFactory = $this->identityFactoryFactory->createService($serviceLocator);

        $this->assertInstanceOf(CacheableIdentityFactory::class, $identityFactory);
    }

    public function testItCreatesDoctrineIdentityFactoryIfCacheIsDisabled()
    {
        $serviceLocator = $this->getServiceLocator([
            'config' => [
                'cache' => [
                    'identity_factory' => [
                        'enabled' => false,
                        'options' => [
                            'ttl' => 300,
                        ],
                    ],
                ],
            ],
            EntityManager::class => $this->entityManager,
            Cache::class => $this->cache,
            'Feature\FeatureToggles' => $this->featureToggles,
        ]);

        $identityFactory = $this->identityFactoryFactory->createService($serviceLocator);

        $this->assertInstanceOf(DoctrineIdentityFactory::class, $identityFactory);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getServiceLocator(array $services)
    {
        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);

        $serviceLocator->expects($this->any())
            ->method('get')
            ->with(call_user_func_array([$this, 'logicalOr'], array_keys($services)))
            ->will($this->returnCallback(function ($serviceName) use ($services) {
                return $services[$serviceName];
            }));

        return $serviceLocator;
    }
}
