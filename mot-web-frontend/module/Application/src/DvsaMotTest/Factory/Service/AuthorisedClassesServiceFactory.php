<?php

namespace DvsaMotTest\Factory\Service;

use DvsaMotTest\Service\AuthorisedClassesService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class AuthorisedClassesServiceFactory.
 */
class AuthorisedClassesServiceFactory implements FactoryInterface
{
    /**
     * Create AuthorisedClassesService.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return AuthorisedClassesService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $restClient = $serviceLocator->get(HttpRestJsonClient::class);

        $authorisedClassesService = new AuthorisedClassesService(
            $restClient
        );

        return $authorisedClassesService;
    }
}
