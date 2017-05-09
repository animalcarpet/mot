<?php

namespace Dashboard\Factory\Service;

use Application\Data\ApiPersonalDetails;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dashboard\Service\TradeRolesAssociationsService;

class TradeRolesAssociationsServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TradeRolesAssociationsService(
            $serviceLocator->get(ApiPersonalDetails::class),
            $serviceLocator->get('CatalogService')
        );
    }
}
