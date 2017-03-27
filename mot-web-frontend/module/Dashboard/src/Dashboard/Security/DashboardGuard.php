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

    const HIGH_AUTHORITY_DVSA_ROLES = [
        RoleCode::SCHEME_USER,
        RoleCode::SCHEME_MANAGER,
    ];

    /** @var MotAuthorisationServiceInterface $authorisationService */
    protected $authorisationService;

    /** @var bool $overdueSpecialNoticeAssertionFailure */
    private $overdueSpecialNoticeAssertionFailure = false;

    /** @var int $overdueSpecialNoticeCount */
    private $overdueSpecialNoticeCount = 0;

    /** @var bool $hasTestInProgress */
    private $hasTestInProgress = false;

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
    public function canViewReplacementDuplicateCertificateLink()
    {
        if ($this->hasHighAuthorityTradeRole() || $this->isTester()) {
            if ($this->isDemoTestNeeded() && !$this->isQualifiedTester()) {
                return false;
            }
            if ($this->hasTestInProgress){
                return false;
            }

            return true;
        }

        if ($this->isCustomerServiceOperative()) {
            return true;
        }

        if ($this->isDvlaOperative()) {
            return true;
        }

        if ($this->isVehicleExaminer()) {
            return true;
        }

        if ($this->hasHighAuthorityDvsaRole()) {
            return true;
        }

        if ($this->isAreaOffice1()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canViewSlotBalance()
    {
        if (!$this->hasHighAuthorityTradeRole()) {
            return false;
        }

        if (in_array(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, $this->getAllRoles())) {
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
    public function isDemoTestNeeded()
    {
        $isDemoTestRequired = in_array(RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, $this->getAllRoles());

        return $this->isTester() ? $isDemoTestRequired && !$this->isTesterActive() : $isDemoTestRequired;
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
    public function canViewAeInformationLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_LIST);
    }

    /**
     * @return bool
     */
    public function canViewSiteInformationLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DVSA_SITE_SEARCH);
    }

    /**
     * @return bool
     */
    public function canViewMotFormsLink()
    {
        if ($this->isTester())
        {
            if (!$this->isDemoTestNeeded() && !$this->overdueSpecialNoticeCount > 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canViewUserSearchLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::USER_SEARCH);
    }

    /**
     * @return bool
     */
    public function canViewMotTestsLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DVSA_SITE_SEARCH);
    }

    /**
     * @return bool
     */
    public function canViewDemoTestRequestsLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE);
    }

    /**
     * @return bool
     */
    public function canViewVehicleSearchLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW);
    }

    /**
     * @return bool
     */
    public function canViewPaymentsLink()
    {
        if ($this->hasViewPaymentsPermissionButRoleShouldNotViewPaymentsLink()) {
            return false;
        }

        return $this->authorisationService->isGranted(PermissionInSystem::SLOTS_TRANSACTION_READ_FULL);
    }

    /**
     * @return bool
     */
    protected function hasViewPaymentsPermissionButRoleShouldNotViewPaymentsLink()
    {
        $allRoles = $this->getAllRoles();

        return in_array(RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, $allRoles) ||
            in_array(RoleCode::AUTHORISED_EXAMINER_DELEGATE, $allRoles);
    }

    /**
     * @return bool
     */
    public function canViewDirectDebitLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SLOTS_DIRECT_DEBIT_SEARCH);
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
    public function canViewGenerateSurveyReportLink()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT);
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
    public function canViewYourPerformance()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::DISPLAY_TESTER_STATS_BOX);
    }

    /**
     * @return bool
     */
    public function canPerformNonMotTest()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM);
    }

    /**
     * @return bool
     */
    public function canCreateAuthorisedExaminer()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_CREATE);
    }

    /**
     * @return bool
     */
    public function canCreateVehicleTestingStation()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::VEHICLE_TESTING_STATION_CREATE);
    }

    /**
     * @return bool
     */
    public function canGenerateFinancialReports()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::SLOTS_REPORTS_GENERATE);
    }

    /**
     * @return bool
     */
    public function isQualifiedTester()
    {
        return $this->isTester() && $this->isTesterActive();
    }

    /**
     * @return bool
     */
    public function isTester()
    {
        return in_array(RoleCode::TESTER, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    private function isTesterActive()
    {
        return in_array(RoleCode::TESTER_ACTIVE, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    private function isCustomerServiceOperative()
    {
        return in_array(RoleCode::CUSTOMER_SERVICE_OPERATIVE, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    private function isDvlaOperative()
    {
        return in_array(RoleCode::DVLA_OPERATIVE, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    public function isVehicleExaminer()
    {

        return in_array(RoleCode::VEHICLE_EXAMINER, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    public function isAreaOffice1()
    {
        return in_array(RoleCode::AREA_OFFICE_1, $this->getAllRoles());
    }

    /**
     * @return bool
     */
    private function hasHighAuthorityTradeRole()
    {
        return !empty(array_intersect($this->getAllRoles(), self::HIGH_AUTHORITY_TRADE_ROLES));
    }

    /**
     * @return bool
     */
    private function hasHighAuthorityDvsaRole()
    {
        return !empty(array_intersect($this->getAllRoles(), self::HIGH_AUTHORITY_DVSA_ROLES));
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
     * @param int $overdueSpecialNoticeCount
     *
     * @return $this
     */
    public function setOverdueSpecialNoticeCount($overdueSpecialNoticeCount)
    {
        $this->overdueSpecialNoticeCount = $overdueSpecialNoticeCount;

        return $this;
    }

    /**
     * @param bool $hasTestInProgress
     *
     * @return $this
     */
    public function setHasTestInProgress($hasTestInProgress)
    {
        $this->hasTestInProgress = $hasTestInProgress;

        return $this;
    }

    /**
     * @return array
     */
    private function getAllRoles()
    {
        return $this->authorisationService->getAllRoles();
    }
}
