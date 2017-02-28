<?php

namespace Dashboard\Authorisation;

use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::USER,
        RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
        RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED,
        RoleCode::SITE_ADMIN,
        RoleCode::SITE_MANAGER,
        RoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
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

        if ($this->doesUserHaveNoRoles($userRoles)) {
            return true;
        }

        foreach ($userRoles as $role) {
            if (!in_array($role, self::ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $userRoles
     *
     * @return bool
     */
    private function doesUserHaveNoRoles(array $userRoles)
    {
        return count($userRoles) == 1 && in_array(RoleCode::USER, $userRoles);
    }
}
