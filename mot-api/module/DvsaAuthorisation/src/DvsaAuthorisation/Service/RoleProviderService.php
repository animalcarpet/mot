<?php

namespace DvsaAuthorisation\Service;

use DvsaEntities\Repository\RbacRepository;
use DvsaEntities\Entity\Person;

/**
 * Class RoleProviderService.
 *
 * Allows for queries against the database to retrieve roles for users.
 */
class RoleProviderService
{
    private $rbacRepository;

    /**
     * @param RbacRepository $rbacRepository
     */
    public function __construct($rbacRepository)
    {
        $this->rbacRepository = $rbacRepository;
    }

    public function getRolesForPerson(Person $person)
    {
        $roles = $this->rbacRepository->authorizationDetails($person->getId())->asArray();
        $results = [];
        foreach ($roles['sites'] as $site) {
            foreach ($site['roles'] as $id => $role) {
                $results[] = $role;
            }
        }
        foreach ($roles['organisations'] as $site) {
            foreach ($site['roles'] as $id => $role) {
                $results[] = $role;
            }
        }
        foreach ($roles['normal']['roles'] as $id => $role) {
            $results[] = $role;
        }

        return $results;
    }
}
