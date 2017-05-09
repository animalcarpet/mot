<?php

namespace UserAdmin\Factory;

use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

/**
 * Class UserAdminSessionManagerFactory.
 */
class UserAdminSessionManagerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserAdminSessionManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authorisationService = $serviceLocator->get('AuthorisationService');
        $container = new Container(UserAdminSessionManager::USER_ADMIN_SESSION_NAME);

        return new UserAdminSessionManager($container, $authorisationService);
    }
}
