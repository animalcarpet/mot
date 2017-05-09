<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Service\OrganisationRoleService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class OrganisationRoleServiceFactory.
 */
class OrganisationRoleServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OrganisationRoleService(
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(EntityManager::class)->getRepository(Organisation::class),
            $serviceLocator->get(EntityManager::class)->getRepository(Person::class),
            $serviceLocator->get(RoleAvailability::class),
            $serviceLocator->get('DvsaAuthenticationService')
        );
    }
}
