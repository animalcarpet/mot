<?php

namespace Dashboard\Authorisation;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\RoleCode;

class ViewNewHomepageAssertion
{
    const ROLES_ALLOWED_TO_VIEW_NEW_HOMEPAGE = [
        RoleCode::AREA_OFFICE_1,
        RoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        RoleCode::CENTRAL_ADMIN_TEAM,
        RoleCode::CUSTOMER_SERVICE_OPERATIVE,
        RoleCode::CUSTOMER_SERVICE_MANAGER,
        RoleCode::DVLA_OPERATIVE,
        RoleCode::FINANCE,
        RoleCode::SCHEME_MANAGER,
        RoleCode::SCHEME_USER,
        RoleCode::SITE_ADMIN,
        RoleCode::SITE_MANAGER,
        RoleCode::TESTER,
        RoleCode::TESTER_ACTIVE,
        RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
        RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED,
        RoleCode::USER,
        RoleCode::VEHICLE_EXAMINER,
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
