<?php

namespace Dashboard\Authorisation;

use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::USER,
        RoleCode::TESTER_ACTIVE,
        RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
        RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED,
    ];

    /** @var PersonAuthorization $personAuthorization */
    private $personAuthorization;

    /**
     * UserAuthorisationHelper constructor.
     *
     * @param PersonAuthorization $personAuthorization
     */
    public function __construct(PersonAuthorization $personAuthorization)
    {
        $this->personAuthorization = $personAuthorization;
    }

    /**
     * @return bool
     */
    public function canViewNewHomepage()
    {
        $userRoles = $this->personAuthorization->getAllRoles();
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
