<?php

namespace Application\Listener\Factory;

use Account\Service\ExpiredPasswordService;
use Application\Listener\ExpiredPasswordListener;
use DvsaCommon\Date\DateTimeHolder;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExpiredPasswordListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ExpiredPasswordListener(
            $serviceLocator->get('MotIdentityProvider'),
            new DateTimeHolder(),
            $serviceLocator->get('Application\Logger'),
            $serviceLocator->get(ExpiredPasswordService::class)
        );
    }
}
