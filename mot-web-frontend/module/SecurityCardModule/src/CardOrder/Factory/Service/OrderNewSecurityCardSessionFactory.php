<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use DvsaClient\MapperFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class OrderNewSecurityCardSessionFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderNewSecurityCardSessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer =  new Container(OrderNewSecurityCardSessionService::UNIQUE_KEY);

        return new OrderNewSecurityCardSessionService(
            $sessionContainer,
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
