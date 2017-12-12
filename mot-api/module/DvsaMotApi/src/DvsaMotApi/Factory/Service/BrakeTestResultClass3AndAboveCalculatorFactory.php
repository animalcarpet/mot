<?php

namespace DvsaMotApi\Factory\Service;

use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BrakeTestResultClass3AndAboveCalculatorFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return BrakeTestResultClass3AndAboveCalculator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new BrakeTestResultClass3AndAboveCalculator(
            $featureToggles
        );
    }
}