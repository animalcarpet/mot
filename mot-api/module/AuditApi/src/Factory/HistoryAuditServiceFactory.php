<?php

namespace Dvsa\Mot\AuditApi\Factory;

use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HistoryAuditServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $historyAuditService = new HistoryAuditService(
            $serviceLocator->get('doctrine.entitymanager.orm_default'),
            null,
            $serviceLocator->get('Application\Logger')
    );

        return $historyAuditService;
    }
}
