<?php

namespace Dashboard\Security;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;

class DashboardGuard
{
    /** @var MotAuthorisationServiceInterface $authorisationService */
    protected $authorisationService;

    /**
     * DashboardGuard constructor.
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
    public function canPerformDemoTest()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM);
    }

    /**
     * @return bool
     */
    public function isDemoTestNeeded()
    {
        return ($this->authorisationService->hasRole(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED)
               && !$this->authorisationService->hasRole(RoleCode::TESTER_ACTIVE));
    }
}
