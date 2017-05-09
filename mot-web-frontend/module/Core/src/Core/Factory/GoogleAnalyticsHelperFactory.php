<?php

namespace Core\Factory;

use Core\Helper\GoogleAnalyticsHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GoogleAnalyticsHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->getServiceLocator()->get('config');

        if (!isset($config['gaTrackingCode'])) {
            throw new \Exception('Google Analytics tracking code not found in config.');
        }

        $gaTrackingCode = $config['gaTrackingCode'];

        return new GoogleAnalyticsHelper($gaTrackingCode);
    }
}
