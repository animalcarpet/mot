<?php

namespace DvsaAuthorisation\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;

/**
 * Extends the MotAuthorizationServiceInterface for extra methods that are used on the service tier.
 */
interface AuthorisationServiceInterface extends MotAuthorisationServiceInterface
{
    /**
     * Returns the current user's authorization data as an array for sending as JSON.
     */
    public function getAuthorizationDataAsArray();

    /**
     * Does specified person have the specified role?
     *
     * @param $person
     * @param $roleName
     *
     * @return bool
     *
     * @deprecated check permissions, rather than roles
     */
    public function personHasRole($person, $roleName);

    /**
     * For use by framework-related code only, refreshes the roles and permissions.
     */
    public function flushAuthorisationCache();
}
