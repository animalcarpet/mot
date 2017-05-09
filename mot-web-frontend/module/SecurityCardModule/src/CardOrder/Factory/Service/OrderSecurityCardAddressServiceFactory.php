<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Application\Data\ApiPersonalDetails;
use Zend\ServiceManager\FactoryInterface;

class OrderSecurityCardAddressServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderSecurityCardAddressService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var OrderNewSecurityCardSessionService $securityCardSessionService */
        $securityCardSessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        /** @var ApiPersonalDetails $apiPersonalDetails */
        $apiPersonalDetails = $serviceLocator->get(ApiPersonalDetails::class);

        return new OrderSecurityCardAddressService($securityCardSessionService, $apiPersonalDetails);
    }
}
