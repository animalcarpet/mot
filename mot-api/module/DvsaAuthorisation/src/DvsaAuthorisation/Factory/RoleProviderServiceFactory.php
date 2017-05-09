<?php

namespace DvsaAuthorisation\Factory;

use DvsaAuthorisation\Service\RoleProviderService;
use DvsaEntities\Repository\RbacRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RoleProviderServiceFactory.
 */
class RoleProviderServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RoleProviderService($serviceLocator->get(RbacRepository::class));
    }
}
