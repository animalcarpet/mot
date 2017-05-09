<?php

namespace TestSupport\Factory;

use DvsaCommon\HttpRestJson\Client;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Service\UserService;
use TestSupport\Service\AccountDataService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UserService(
            $serviceLocator->get(AccountDataService::class),
            $serviceLocator->get(NotificationsHelper::class),
            $serviceLocator->get(Client::class),
            $serviceLocator->get(TestSupportAccessTokenManager::class)
        );
    }
}
