<?php

namespace Dashboard\Authorisation;

use Dashboard\Model\PersonalDetails;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::USER
    ];

    /** @var PersonalDetails $personalDetails */
    private $personalDetails;

    /**
     * UserAuthorisationHelper constructor.
     *
     * @param PersonalDetails $personalDetails
     */
    public function __construct(PersonalDetails $personalDetails)
    {
        $this->personalDetails = $personalDetails;
    }

    /**
     * @return bool
     */
    public function canViewNewHomepage()
    {
        $userRoles = $this->personalDetails->getRoles();
        if (empty($userRoles)) {
            return true;
        }
        foreach ($userRoles as $role) {
            if (!in_array($role, self::ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE)) {
                return false;
            }
        }

        return true;
    }
}
