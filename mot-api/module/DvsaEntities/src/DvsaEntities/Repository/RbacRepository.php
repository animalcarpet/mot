<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Model\PersonAuthorization;

interface RbacRepository
{
    /**
     * @param int    $personId
     * @param string $roleName
     *
     * @return bool
     */
    public function personIdHasRole($personId, $roleName);

    /**
     * @param int $personId
     *
     * @return PersonAuthorization
     */
    public function authorizationDetails($personId);
}
