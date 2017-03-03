<?php

namespace Dashboard\Security;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;

class DashboardGuard
{
    /** @var MotAuthorisationServiceInterface $authorisationService */
    protected $authorisationService;

    /** @var bool $overdueSpecialNoticeAssertionFailure */
    private $overdueSpecialNoticeAssertionFailure = false;

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
     * OverdueSpecialNoticeAssertion setter
     *
     * Ideally we could just pass a boolean in here, rather than the assertion object. However, the method
     * canPerformTest does not definitively confirm that a tester can perform a test, and it also doesn't just check for
     * overdue special notices (also does some AuthorisationForTestingMotStatusCode stuff).
     *
     * @param OverdueSpecialNoticeAssertion $overdueSpecialNoticeAssertion
     * @return $this
     */
    public function setOverdueSpecialNoticeAssertion(OverdueSpecialNoticeAssertion $overdueSpecialNoticeAssertion)
    {
        $this->overdueSpecialNoticeAssertionFailure = !$overdueSpecialNoticeAssertion->canPerformTest();

        return $this;
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
        return $this->authorisationService->hasRole(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED);
    }

    /**
     * @return bool
     */
    public function canViewYourPerformance()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DISPLAY_TESTER_STATS_BOX);
    }

    /**
     * @return bool
     */
    public function canViewContingencyTests()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DISPLAY_TESTER_CONTINGENCY_BOX) &&
               $this->authorisationService->hasRole(RoleCode::TESTER_ACTIVE);

    }

    /**
     * @return bool
     */
    public function isTester()
    {
        return in_array(RoleCode::TESTER, $this->authorisationService->getAllRoles());
    }

    /**
     * @return bool
     */
    public function canPerformMotTest()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::MOT_TEST_PERFORM) &&
        !$this->overdueSpecialNoticeAssertionFailure;
    }
}
