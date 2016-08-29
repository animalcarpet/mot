<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardEventService;
use DvsaCommon\Date\DateTimeHolder;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class OrderSecurityCardEventServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderSecurityCardEventService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OrderSecurityCardEventService(
            $serviceLocator->get(HttpRestJsonClient::class),
            $serviceLocator->get('MotIdentityProvider'),
            new DateTimeHolder()
        );
    }
}
