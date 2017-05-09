<?php

namespace DashboardTest\Security;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dashboard\Security\DashboardGuard;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;
use PHPUnit_Framework_TestCase;

class DashboardGuardTest extends PHPUnit_Framework_TestCase
{
    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockAuthorisationService;

    /** @var OverdueSpecialNoticeAssertion|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockOverdueSpecialNoticeAssertion;

    protected function setup()
    {
        $this->mockAuthorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->mockOverdueSpecialNoticeAssertion = XMock::of(OverdueSpecialNoticeAssertion::class);
    }

    /**
     * @dataProvider userCanPerformMotTestDataProvider
     *
     * @param bool $hasMotTestStartPermission
     * @param bool $hasNoOverdueSpecialNotices
     * @param bool $userShouldHavePermissionToPerformMotTest
     */
    public function testUserCanPerformMotTest(
        $hasMotTestStartPermission,
        $hasNoOverdueSpecialNotices,
        $userShouldHavePermissionToPerformMotTest
    ) {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::MOT_TEST_START, $hasMotTestStartPermission);
        $this->mockOverdueSpecialNoticeAssertion
            ->method('canPerformTest')
            ->willReturn($hasNoOverdueSpecialNotices);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);
        $dashboardGuard->setOverdueSpecialNoticeAssertion($this->mockOverdueSpecialNoticeAssertion);

        $this->assertEquals($userShouldHavePermissionToPerformMotTest, $dashboardGuard->isTestingEnabled());
    }

    /**
     * @dataProvider userCanViewReplacementDuplicateCertificateLinkDataProvider
     *
     * @param array  $userRoles
     * @param bool   $hasInProgressTest
     * @param string $permission
     * @param bool   $hasPermission
     * @param bool   $expectedResult
     */
    public function testUserCanViewReplacementDuplicateCertificateLink(
        array $userRoles,
        $hasInProgressTest,
        $permission,
        $hasPermission,
        $expectedResult
    ) {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $this->addMethodToMockAuthorisationService('isGranted', $permission, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);
        $dashboardGuard->setHasTestInProgress($hasInProgressTest);

        $this->assertEquals($expectedResult, $dashboardGuard->canViewReplacementDuplicateCertificateLink());
    }

    /**
     * @dataProvider userHasPermissionToViewMotFormsDataProvider
     *
     * @param array $userRoles
     * @param int   $overdueSpecialNoticeCount
     * @param bool  $canViewMotForms
     */
    public function testUserHasPermissionToViewMotForms(array $userRoles, $overdueSpecialNoticeCount, $canViewMotForms)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);
        $dashboardGuard->setOverdueSpecialNoticeCount($overdueSpecialNoticeCount);

        $this->assertEquals($canViewMotForms, $dashboardGuard->canViewMotFormsLink());
    }

    /**
     * @dataProvider testUserIsAssignedToAndCanViewVtsDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserIsAssignedToAndCanViewVts($hasPermission)
    {
        $siteId = 1;
        $this->addMethodToMockAuthorisationService('isGrantedAtSite', PermissionAtSite::VEHICLE_TESTING_STATION_READ, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewVehicleTestingStation($siteId));
    }

    /**
     * @dataProvider userCanViewLinkMethodsInDashboardGuardBasedOnPermissionDataProvider
     *
     * @param string $testMethod
     * @param string $permission
     * @param bool   $hasPermission
     * @param bool   $expectedResult
     */
    public function testUserCanViewLinkMethodsInDashboardGuardBasedOnPermission(
        $testMethod,
        $permission,
        $hasPermission,
        $expectedResult
    ) {
        $this->addMethodToMockAuthorisationService('isGranted', $permission, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($dashboardGuard->$testMethod(), $expectedResult);
    }

    /**
     * @dataProvider userCanViewLinkMethodsInDashboardGuardBasedOnRoleDataProvider
     *
     * @param string $testMethod
     * @param array  $userRoles
     * @param bool   $expectedResult
     */
    public function testUserCanViewLinkMethodsInDashboardGuardBasedOnRole(
        $testMethod,
        $userRoles,
        $expectedResult
    ) {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($dashboardGuard->$testMethod(), $expectedResult);
    }

    /**
     * @dataProvider userCanViewLinkMethodsInDashboardGuardBasedOnPermissionAndRolesDataProvider
     *
     * @param string $testMethod
     * @param array  $userRoles
     * @param string $permission
     * @param bool   $hasPermission
     * @param bool   $expectedResult
     */
    public function testUserCanViewLinkMethodsInDashboardGuardBasedOnPermissionAndRoles(
        $testMethod,
        $userRoles,
        $permission,
        $hasPermission,
        $expectedResult
    ) {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $this->addMethodToMockAuthorisationService('isGranted', $permission, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($dashboardGuard->$testMethod(), $expectedResult);
    }

    public function userCanPerformMotTestDataProvider()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewReplacementDuplicateCertificateLinkDataProvider()
    {
        return [
            [[RoleCode::USER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::SITE_ADMIN], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::SITE_ADMIN], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::TESTER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::TESTER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::AREA_OFFICE_1], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::AREA_OFFICE_1], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::SCHEME_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::SCHEME_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::VEHICLE_EXAMINER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::VEHICLE_EXAMINER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::DVLA_OPERATIVE], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::DVLA_OPERATIVE], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::DVLA_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::DVLA_MANAGER], false, PermissionInSystem::CERTIFICATE_READ, false, false],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false, PermissionInSystem::CERTIFICATE_READ, true, false],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false, PermissionInSystem::CERTIFICATE_READ, true, true],
            [[RoleCode::USER, RoleCode::TESTER], true, PermissionInSystem::CERTIFICATE_READ, true, false],
        ];
    }

    /**
     * @return array
     */
    public function userHasPermissionToViewMotFormsDataProvider()
    {
        return [
            [[RoleCode::USER, RoleCode::TESTER], 0, true],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], 0, false],
            [[RoleCode::USER, RoleCode::TESTER], 1, false],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], 1, false],
        ];
    }

    /**
     * @return array
     */
    public function testUserIsAssignedToAndCanViewVtsDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewLinkMethodsInDashboardGuardBasedOnPermissionDataProvider()
    {
        return [
            ['canViewAeInformationLink', PermissionInSystem::AUTHORISED_EXAMINER_LIST, false, false],
            ['canViewAeInformationLink', PermissionInSystem::AUTHORISED_EXAMINER_LIST, true, true],
            ['canViewUserSearchLink', PermissionInSystem::USER_SEARCH, false, false],
            ['canViewUserSearchLink', PermissionInSystem::USER_SEARCH, true, true],
            ['canViewDemoTestRequestsLink', PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE, false, false],
            ['canViewDemoTestRequestsLink', PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE, true, true],
            ['canViewVehicleSearchLink', PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW, false, false],
            ['canViewVehicleSearchLink', PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW, true, true],
            ['canViewGenerateSurveyReportLink', PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT, false, false],
            ['canViewGenerateSurveyReportLink', PermissionInSystem::GENERATE_SATISFACTION_SURVEY_REPORT, true, true],
            ['canViewVehicleTestingStationList', PermissionInSystem::VEHICLE_TESTING_STATION_LIST, false, false],
            ['canViewVehicleTestingStationList', PermissionInSystem::VEHICLE_TESTING_STATION_LIST, true, true],
            ['canAcknowledgeSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE, false, false],
            ['canAcknowledgeSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE, true, true],
            ['canReadAllSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_READ, false, false],
            ['canReadAllSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_READ, true, true],
            ['canReceiveSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT, false, false],
            ['canReceiveSpecialNotices', PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT, true, true],
            ['canViewYourPerformance', PermissionInSystem::DISPLAY_TESTER_STATS_BOX, false, false],
            ['canViewYourPerformance', PermissionInSystem::DISPLAY_TESTER_STATS_BOX, true, true],
            ['canCreateAuthorisedExaminer', PermissionInSystem::AUTHORISED_EXAMINER_CREATE, false, false],
            ['canCreateAuthorisedExaminer', PermissionInSystem::AUTHORISED_EXAMINER_CREATE, true, true],
            ['canCreateVehicleTestingStation', PermissionInSystem::VEHICLE_TESTING_STATION_CREATE, false, false],
            ['canCreateVehicleTestingStation', PermissionInSystem::VEHICLE_TESTING_STATION_CREATE, true, true],
            ['canViewSiteInformationLink', PermissionInSystem::DVSA_SITE_SEARCH, false, false],
            ['canViewSiteInformationLink', PermissionInSystem::DVSA_SITE_SEARCH, true, true],
            ['canViewMotTestsLink', PermissionInSystem::DVSA_SITE_SEARCH, false, false],
            ['canViewMotTestsLink', PermissionInSystem::DVSA_SITE_SEARCH, true, true],
            ['canGenerateFinancialReports', PermissionInSystem::SLOTS_REPORTS_GENERATE, false, false],
            ['canGenerateFinancialReports', PermissionInSystem::SLOTS_REPORTS_GENERATE, true, true],
            ['canViewSecurityCardOrderListLink', PermissionInSystem::VIEW_SECURITY_CARD_ORDER, false, false],
            ['canViewSecurityCardOrderListLink', PermissionInSystem::VIEW_SECURITY_CARD_ORDER, true, true],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewLinkMethodsInDashboardGuardBasedOnRoleDataProvider()
    {
        return [
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DELEGATE], true],
            ['canViewSlotBalance', [RoleCode::SITE_ADMIN], true],
            ['canViewSlotBalance', [RoleCode::SITE_MANAGER], true],
            ['canViewSlotBalance', [RoleCode::TESTER], false],
            ['canViewSlotBalance', [RoleCode::USER], false],
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false],
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], true],
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE], false],
            ['canViewSlotBalance', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], true],
            ['isDemoTestNeeded', [RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            ['isDemoTestNeeded', [RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            ['isDemoTestNeeded', [RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            ['isDemoTestNeeded', [RoleCode::TESTER, RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false],
            ['isQualifiedTester', [RoleCode::TESTER_ACTIVE, RoleCode::TESTER], true],
            ['isQualifiedTester', [RoleCode::TESTER], false],
            ['isTester', [RoleCode::TESTER], true],
            ['isTester', [RoleCode::AUTHORISED_EXAMINER_DELEGATE], false],
            ['isAreaOffice1', [RoleCode::USER], false],
            ['isAreaOffice1', [RoleCode::AREA_OFFICE_1], true],
            ['isAreaOffice1', [RoleCode::USER, RoleCode::AREA_OFFICE_1], true],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewLinkMethodsInDashboardGuardBasedOnPermissionAndRolesDataProvider()
    {
        return [
            ['canViewPaymentsLink', [RoleCode::FINANCE], PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, true, true],
            ['canViewPaymentsLink', [RoleCode::FINANCE], PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, false, false],
            ['canViewPaymentsLink', [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, true, false],
            ['canViewPaymentsLink', [RoleCode::AUTHORISED_EXAMINER_DELEGATE], PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, true, false],
        ];
    }

    /**
     * @param string     $methodName
     * @param string     $withParameter
     * @param array|bool $returnValue
     */
    private function addMethodToMockAuthorisationService($methodName, $withParameter, $returnValue)
    {
        if (is_null($withParameter)) {
            $this->mockAuthorisationService
                ->method($methodName)
                ->willReturn($returnValue);
        } else {
            $this->mockAuthorisationService
                ->method($methodName)
                ->with($withParameter)
                ->willReturn($returnValue);
        }
    }
}
