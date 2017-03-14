<?php

namespace Dashboard\Authorisation;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::USER,
        RoleCode::TESTER,
        RoleCode::SITE_ADMIN,
        RoleCode::SITE_MANAGER,
        RoleCode::TESTER_ACTIVE,
        RoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
        RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED,
    ];

    /** @var MotAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /**
     * ViewNewHomepageAssertion constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * @return bool
     */
    public function canViewNewHomepage()
    {
        $userRoles = $this->authorisationService->getAllRoles();

        return $this->areAllUserRolesAllowedToViewNewHomepage($userRoles);
    }

    /**
     * @param array $userRoles
     *
     * @return bool
     */
    private function areAllUserRolesAllowedToViewNewHomepage(array $userRoles)
    {
        return empty(array_diff($userRoles, self::ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE));
    }
}
