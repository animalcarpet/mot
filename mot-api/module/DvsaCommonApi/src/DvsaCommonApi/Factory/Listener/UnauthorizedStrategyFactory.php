<?php

namespace DvsaCommonApi\Factory\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonApi\Listener\RestUnauthorizedStrategy;

/**
 * Class UnauthorizedStrategyFactory.
 */
class UnauthorizedStrategyFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RestUnauthorizedStrategy($serviceLocator->get('DvsaAuthenticationService'));
    }
}
