<?php

namespace UserAdmin\Factory\Service;

use DvsaClient\MapperFactory;
use UserAdmin\Service\UserAdminSessionService;
use Session\Container;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserAdminSessionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return UserAdminSessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UserAdminSessionService(
            new Container(UserAdminSessionService::UNIQUE_KEY),
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
