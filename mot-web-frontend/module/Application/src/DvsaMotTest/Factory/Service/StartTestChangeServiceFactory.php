<?php

namespace DvsaMotTest\Factory\Service;

use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Service\StartTestSessionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url;

class StartTestChangeServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new StartTestChangeService(
            $serviceLocator->get(StartTestSessionService::class),
            $serviceLocator->get(Url::class)
        );
    }
}