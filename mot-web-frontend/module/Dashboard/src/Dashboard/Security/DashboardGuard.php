<?php

namespace Dashboard\Security;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;

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
}
