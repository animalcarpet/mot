<?php

namespace SiteApi\Model\RoleRestriction;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use SiteApi\Model\SitePersonnel;

/**
 * Class SiteAdminRestriction.
 */
class SiteAdminRestriction extends AbstractSiteRoleRestriction
{
    /**
     * Checks if all requirements are met to assign a role to the user in the given organisation.
     * Return unmet conditions.
     *
     * @param Person        $person
     * @param SitePersonnel $personnel
     *
     * @return ErrorSchema
     */
    public function verify(Person $person, SitePersonnel $personnel)
    {
        return parent::verify($person, $personnel);
    }

    /**
     * @return string The role this restriction applies to
     */
    public function getRole()
    {
        return SiteBusinessRoleCode::SITE_ADMIN;
    }
}
