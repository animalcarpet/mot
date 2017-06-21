<?php

namespace DvsaEntities\Factory\Audit;

use DvsaEntities\Audit\EntityAuditListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityAuditListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new EntityAuditListener($serviceLocator);
    }
}