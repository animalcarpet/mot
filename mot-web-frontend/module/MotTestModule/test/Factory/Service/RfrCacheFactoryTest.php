<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Factory\Service;

use Dvsa\Mot\Frontend\MotTestModule\Factory\Service\RfrCacheFactory;
use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCache;
use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCacheKeyGenerator;
use DvsaCommon\Cache\Cache;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Zend\Cache\Storage\Adapter\Apc as ApcAdapter;
use Zend\Cache\Storage\Adapter\Memcached as MemcachedAdapter;
use DvsaApplicationLogger\Log\Logger;

class RfrCacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var FeatureToggles | MockObject */
    private $featureToggles;
    /** @var MotConfig | MockObject*/
    private $motConfig;
    /** @var StorageInterface | MockObject */
    private $storage;
    /** @var  RfrCacheKeyGenerator | MockObject*/
    private $keyGenerator;
    /** @var ServiceLocatorInterface | MockObject */
    private $serviceLocator;
    /** @var Logger | MockObject */
    private $logger;

    /** @var  RfrCacheFactory */
    private $sut;

    public function setUp()
    {
        $this->serviceLocator = XMock::of(ServiceLocatorInterface::class);
        $this->motConfig = XMock::of(MotConfig::class);
        $this->storage = XMock::of(StorageInterface::class);
        $this->keyGenerator = XMock::of(RfrCacheKeyGenerator::class);
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->logger = XMock::of(Logger::class);

        $this->serviceLocator
            ->expects($this->at(0))
            ->method('get')
            ->with(MotConfig::class)
            ->willReturn($this->motConfig);

        $this->serviceLocator
            ->expects($this->at(1))
            ->method('get')
            ->with(RfrCacheKeyGenerator::class)
            ->willReturn($this->keyGenerator);

        $this->serviceLocator
            ->expects($this->at(2))
            ->method('get')
            ->with('Feature\FeatureToggles')
            ->willReturn($this->featureToggles);

        $this->serviceLocator
            ->expects($this->at(3))
            ->method('get')
            ->with('Application\Logger')
            ->willReturn($this->logger);

        $this->sut = new RfrCacheFactory();
    }

    /**
     * @param array $config
     */
    private function withConfig(array $config)
    {
        $this->motConfig
            ->expects($this->once())
            ->method('get')
            ->with(RfrCacheFactory::RFR_CACHE_CONFIG_KEY)
            ->willReturn($config);
    }

    private function getDefaultMemcachedConfig()
    {
        return [
            'adapter' => [
                'name'    => 'memcached',
                'options' => [
                    'ttl' => 3600,
                    'namespace' => 'rfr_cache',
                    'servers' => [ 'elasticache-host' , 11211]
                ],
            ],
            'plugins' => [
                'exception_handler' => ['throw_exceptions' => true],
            ],
        ];
    }

    public function testCreateService_withValidMemcachedConfig_shouldReturnCacheInstance()
    {
        $this->withConfig($this->getDefaultMemcachedConfig());
        /** @var Cache $result */
        $result = $this->sut->createService($this->serviceLocator);

        $this->assertInstanceOf(RfrCache::class, $result);
        $this->assertInstanceOf(MemcachedAdapter::class, $result->getStorage());
        $this->assertInstanceOf(RfrCacheKeyGenerator::class, $result->getCacheKeyGenerator());
    }
}