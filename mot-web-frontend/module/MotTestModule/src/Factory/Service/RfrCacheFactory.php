<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Service;

use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCache;
use Dvsa\Mot\Frontend\MotTestModule\Service\RfrCacheKeyGenerator;
use DvsaCommon\Configuration\MotConfig;
use DvsaFeature\FeatureToggles;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\Exception\InvalidArgumentException;

class RfrCacheFactory implements FactoryInterface
{
    const RFR_CACHE_CONFIG_KEY = 'rfr_cache';

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $motConfig */
        $motConfig = $serviceLocator->get(MotConfig::class);
        $rfrCacheConfig = $motConfig->get(self::RFR_CACHE_CONFIG_KEY);

        $rfrCacheConfig = $this->sanitiseConfigForAPC($rfrCacheConfig);

        /** @var StorageInterface $storage */
        $storage = StorageFactory::factory($rfrCacheConfig);

        /** @var RfrCacheKeyGenerator $cacheKeyGenerator */
        $cacheKeyGenerator = $serviceLocator->get(RfrCacheKeyGenerator::class);
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new RfrCache(
            $storage,
            $cacheKeyGenerator,
            $featureToggles
        );
    }

    /**
     * If apc storage adapter is used - make sure that the option settings don't contain any additional parameters
     *
     * @param array $rfrCacheConfig
     * @return array
     */
    public function sanitiseConfigForAPC(array $rfrCacheConfig)
    {
        if(!$this->isApcEnabled($rfrCacheConfig)){
            return $rfrCacheConfig;
        }

        $filteredOptions = $this->filterAdapterOptions($rfrCacheConfig);
        $rfrCacheConfig['adapter']['options'] = $filteredOptions;

        return $rfrCacheConfig;
    }

    /**
     * @param array $rfrCacheConfig
     * @return array|bool
     */
    private function isApcEnabled(array $rfrCacheConfig)
    {
        if(!isset($rfrCacheConfig['adapter']) || !isset($rfrCacheConfig['adapter']['name'])) {
            return $rfrCacheConfig;
        }

        $adapterName = $rfrCacheConfig['adapter']['name'];

        return strcasecmp($adapterName, 'apc') === 0 ;
    }

    /**
     * @param array $rfrCacheConfig
     * @return array
     */
    private function filterAdapterOptions(array $rfrCacheConfig)
    {
        if(!isset($rfrCacheConfig['adapter']['options'])){
            return [];
        }

        $allowedOptions = $this->getAllowedApcOptions();

        $filteredOptions = array_filter(
            $rfrCacheConfig['adapter']['options'],
            function($key) use ($allowedOptions) {
                return in_array($key,$allowedOptions);
            },
            ARRAY_FILTER_USE_KEY
        );

        return $filteredOptions;
    }

    /**
     * @return array
     */
    private function getAllowedApcOptions()
    {
        return [
            'ttl',
            'namespace',
            'key_pattern',
            'readable',
            'writable',
        ];
    }
}