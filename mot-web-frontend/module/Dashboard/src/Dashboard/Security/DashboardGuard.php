<?php

namespace Dashboard\Security;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
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
    public function canViewVehicleTestingStationList()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::VEHICLE_TESTING_STATION_LIST);
    }

    /**
     * @param int $siteId
     *
     * @return bool
     */
    public function canViewVehicleTestingStation($siteId)
    {
        return $this->authorisationService->isGrantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $siteId);
    }

    /**
     * @return bool
     */
    public function canViewReplacementDuplicateCertificateLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::CERTIFICATE_SEARCH);
    }

    /**
     * @return bool
     */
    public function canViewSlotBalance()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SLOTS_VIEW);
    }

    /**
     * @return bool
     */
    public function canAcknowledgeSpecialNotices()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE);
    }

    /**
     * @return bool
     */
    public function canReadAllSpecialNotices()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ);
    }

    /**
     * @return bool
     */
    public function canReceiveSpecialNotices()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT);
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
