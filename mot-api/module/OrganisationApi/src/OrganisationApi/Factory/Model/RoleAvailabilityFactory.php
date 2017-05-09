<?php

namespace OrganisationApi\Factory\Model;

use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Model\RoleRestriction\AedmRestriction;
use OrganisationApi\Model\RoleRestriction\AedRestriction;
use OrganisationApi\Model\RoleRestrictionsSet;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RoleAvailabilityFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $authService = $serviceLocator->get('DvsaAuthorisationService');

        return new RoleAvailability(
            new RoleRestrictionsSet(
                [
                    new AedRestriction($authService),
                    new AedmRestriction($authService),
                ]
            ),
            $authService,
            $serviceLocator->get(\Doctrine\ORM\EntityManager::class)->getRepository(\DvsaEntities\Entity\OrganisationBusinessRole::class)
        );
    }
}
