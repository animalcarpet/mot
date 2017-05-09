<?php

namespace TestSupport\Factory;

use TestSupport\Service\FeaturesService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for FeaturesService instances.
 */
class FeaturesServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return \TestSupport\Service\FeaturesService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $legacyPath = __DIR__.'/../../../../../../mot-web-frontend/config/autoload/features.local.php';

        $path = isset($config['featureToggleConfigPath']) ? $config['featureToggleConfigPath'] : $legacyPath;

        return new FeaturesService($path);
    }
}
