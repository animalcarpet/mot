<?php

namespace DvsaAuthorisation\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for {@link UserRoleService}.
 */
class UserRoleServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(\Doctrine\ORM\EntityManager::class);

        return new UserRoleService(
            $entityManager->getRepository(OrganisationBusinessRoleMap::class),
            $entityManager->getRepository(SiteBusinessRoleMap::class),
            $entityManager->getRepository(PersonSystemRoleMap::class)
        );
    }
}
