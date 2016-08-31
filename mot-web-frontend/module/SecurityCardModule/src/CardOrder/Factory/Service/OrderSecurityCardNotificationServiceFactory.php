<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardNotificationService;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\HttpRestJson\Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrderSecurityCardNotificationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderSecurityCardNotificationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $client = $serviceLocator->get(Client::class);

        $dateTimeHolder = new DateTimeHolder();

        return new OrderSecurityCardNotificationService($client, $dateTimeHolder);
    }
}
