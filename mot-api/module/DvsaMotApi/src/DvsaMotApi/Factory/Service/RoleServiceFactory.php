<?php

namespace DvsaMotApi\Factory\Service;

use DvsaRbac\Service\RoleService;
use DvsaEntities\Repository\RbacRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RoleServiceFactory.
 */
class RoleServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RoleService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(RbacRepository::class)
        );
    }
}
