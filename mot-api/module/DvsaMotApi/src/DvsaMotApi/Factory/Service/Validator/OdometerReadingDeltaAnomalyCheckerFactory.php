<?php

namespace DvsaMotApi\Factory\Service\Validator;

use DvsaMotApi\Service\Validator\Odometer\OdometerReadingDeltaAnomalyChecker;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OdometerReadingDeltaAnomalyCheckerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OdometerReadingDeltaAnomalyChecker($serviceLocator->get('ConfigurationRepository'));
    }
}
