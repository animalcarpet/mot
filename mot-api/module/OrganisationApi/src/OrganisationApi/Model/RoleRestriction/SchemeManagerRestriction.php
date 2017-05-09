<?php

namespace OrganisationApi\Model\RoleRestriction;

use DvsaCommon\Constants\Role;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use OrganisationApi\Model\OrganisationPersonnel;
use OrganisationApi\Model\RoleRestrictionInterface;

/**
 * Class AedmRestriction.
 */
class SchemeManagerRestriction implements RoleRestrictionInterface
{
    const ERROR = 'You cannot assign an DSM to anybody';

    /**
     * @param Person                $person
     * @param OrganisationPersonnel $personnel
     *
     * @return ErrorSchema
     */
    public function verify(Person $person, OrganisationPersonnel $personnel)
    {
        $errors = new ErrorSchema();

        $errors->add(self::ERROR);

        return $errors;
    }

    public function getRole()
    {
        return Role::DVSA_SCHEME_MANAGEMENT;
    }
}
