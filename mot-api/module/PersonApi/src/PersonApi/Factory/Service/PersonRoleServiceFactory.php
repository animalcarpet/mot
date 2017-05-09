<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Entity\Role;
use DvsaEntities\Entity\PermissionToAssignRoleMap;
use DvsaEntities\Repository\RbacRepository;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaMotApi\Helper\RoleNotificationHelper;
use PersonApi\Service\PersonRoleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonRoleServiceFactory implements FactoryInterface
{
    /**
     * Create an instance of Person Role service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PersonRoleService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $em */
        $entityManager = $serviceLocator->get(EntityManager::class);

        $personRoleService = new PersonRoleService(
            $serviceLocator->get(RbacRepository::class),
            $entityManager->getRepository(BusinessRoleStatus::class),
            $entityManager->getRepository(PermissionToAssignRoleMap::class),
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(PersonSystemRole::class),
            $entityManager->getRepository(PersonSystemRoleMap::class),
            $entityManager->getRepository(Role::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(RoleEventHelper::class),
            $serviceLocator->get(RoleNotificationHelper::class)
        );

        return $personRoleService;
    }
}
