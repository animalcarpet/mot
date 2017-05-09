<?php

namespace DvsaMotApi\Factory\Service;

use DvsaMotApi\Service\MotTestStatusChangeNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestStatusChangeNotificationFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotTestStatusChangeNotificationService();
    }
}
