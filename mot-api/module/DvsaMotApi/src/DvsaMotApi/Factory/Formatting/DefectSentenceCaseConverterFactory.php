<?php

namespace DvsaMotApi\Factory\Formatting;

use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for DefectSentenceCaseConverterFactory
 */
class DefectSentenceCaseConverterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new DefectSentenceCaseConverter($featureToggles);
    }
}
