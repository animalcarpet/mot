<?php

namespace Dashboard\Security;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;

class DashboardGuard
{
    const HIGH_AUTHORITY_TRADE_ROLES = [
        RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        RoleCode::AUTHORISED_EXAMINER_DELEGATE,
        RoleCode::SITE_MANAGER,
        RoleCode::SITE_ADMIN,
    ];

    const TESTER_WITH_DEMO_TEST_NEEDED_ROLES = [
        RoleCode::USER,
        RoleCode::TESTER,
        RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
    ];

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
     * @param OverdueSpecialNoticeAssertion $overdueSpecialNoticeAssertion
     *
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
        $userRoles = $this->authorisationService->getAllRoles();

        if ($this->hasHighAuthorityTradeRole($userRoles) || $this->isTester()) {
            if ($this->isDemoTestNeeded() && !$this->isQualifiedTester($userRoles)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canViewSlotBalance()
    {
        $roles = $this->authorisationService->getAllRoles();

        $usersHighAuthorityTradeRoles = array_intersect($roles, self::HIGH_AUTHORITY_TRADE_ROLES);
        if (empty($usersHighAuthorityTradeRoles)) {
            return false;
        }

        if (in_array(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, $roles)) {
            return false;
        }

        if ($this->isQualifiedTester()) {
            return false;
        }

        return true;
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
        $roles = $this->authorisationService->getAllRoles();
        $isDemoTestRequired = in_array(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, $roles);

        return $this->isTester() ? $isDemoTestRequired && !$this->isTesterActive($roles) : $isDemoTestRequired;
    }

    /**
     * @return bool
     */
    public function isTestingEnabled()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::MOT_TEST_START) &&
        !$this->overdueSpecialNoticeAssertionFailure;
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
    public function canViewYourPerformance()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DISPLAY_TESTER_STATS_BOX);
    }

    /**
     * @return bool
     */
    public function isQualifiedTester()
    {
        $roles = $this->authorisationService->getAllRoles();

        return in_array(RoleCode::TESTER, $roles) && $this->isTesterActive($roles);
    }

    /**
     * @param $userRoles
     *
     * @return bool
     */
    private function hasHighAuthorityTradeRole($userRoles)
    {
        return !empty(array_intersect($userRoles, self::HIGH_AUTHORITY_TRADE_ROLES));
    }

    /**
     * @param $userRoles
     *
     * @return bool
     */
    public function isTesterActive($userRoles)
    {
        return in_array(RoleCode::TESTER_ACTIVE, $userRoles);
    }
}

