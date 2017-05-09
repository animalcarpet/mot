<?php

namespace DvsaAuthorisation\Factory;

use DvsaAuthorisation\Service\SiteBusinessRoleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteBusinessRoleServiceFactory.
 */
class SiteBusinessRoleServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceManager)
    {
        return new SiteBusinessRoleService($serviceManager->get(\Doctrine\ORM\EntityManager::class));
    }
}
