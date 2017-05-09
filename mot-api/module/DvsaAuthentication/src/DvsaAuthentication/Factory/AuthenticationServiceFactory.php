<?php

namespace DvsaAuthentication\Factory;

use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMApiTokenBasedAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\NonPersistent;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthenticationServiceFactory.
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authService = new AuthenticationService();
        /** @var OpenAMApiTokenBasedAdapter $adapter */
        $adapter = $serviceLocator->get(OpenAMApiTokenBasedAdapter::class);
        $authService->setAdapter($adapter);
        $authService->setStorage(new NonPersistent());

        return $authService;
    }
}
