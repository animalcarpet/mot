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
     * @param array $userRoles
     * @param bool  $hasInProgressTest
     * @param bool  $hasPermission
     */
    public function testUserCanViewReplacementDuplicateCertificateLink(array $userRoles, $hasInProgressTest, $hasPermission)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);
        $dashboardGuard->setHasTestInProgress($hasInProgressTest);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewReplacementDuplicateCertificateLink());
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
     * @dataProvider userHasPermissionDataProvider
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
     * @dataProvider rolesWithViewPaymentLinkPermissionThatShouldNotSeePaymentLink
     *
     * @param string $role
     */
    public function testSomeRolesWithViewPaymentsPermissionCannotViewPaymentsLink($role)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, [$role]);
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertFalse($dashboardGuard->canViewPaymentsLink());
    }

    public function testRoleWithSlotsTransactionReadFullPermissionCanViewPaymentsLink()
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, [RoleCode::FINANCE]);
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->canViewPaymentsLink());
    }

    public function testRoleWithoutSlotsTransactionReadFullPermissionCannotViewPaymentsLink()
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, [RoleCode::FINANCE]);
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SLOTS_TRANSACTION_READ_FULL, false);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertFalse($dashboardGuard->canViewPaymentsLink());
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserCanGenerateFinancialReports($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SLOTS_REPORTS_GENERATE,
            $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canGenerateFinancialReports());
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
    )
    {
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
    )
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
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
    public function userHasPermissionToViewContingencyTestsDataProvider()
    {
        return [
            [[RoleCode::TESTER_ACTIVE, RoleCode::TESTER], true],
            [[RoleCode::TESTER], false],
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
    public function userCanViewLinkMethodsDataProvider()
    {
        return [
            ['canViewAeInformationLink', 'isGranted', PermissionInSystem::AUTHORISED_EXAMINER_LIST, false, false],
            ['canViewAeInformationLink', 'isGranted', PermissionInSystem::AUTHORISED_EXAMINER_LIST, true, true],
            ['canViewSiteInformationLink', 'isGranted', PermissionInSystem::DVSA_SITE_SEARCH, false, false],
            ['canViewSiteInformationLink', 'isGranted', PermissionInSystem::DVSA_SITE_SEARCH, true, true],
            ['canViewUserSearchLink', 'isGranted', PermissionInSystem::USER_SEARCH, false, false],
            ['canViewUserSearchLink', 'isGranted', PermissionInSystem::USER_SEARCH, true, true],
            ['canViewMotTestsLink', 'isGranted', PermissionInSystem::DVSA_SITE_SEARCH, false, false],
            ['canViewMotTestsLink', 'isGranted', PermissionInSystem::DVSA_SITE_SEARCH, true, true],
            ['canViewDemoTestRequestsLink', 'isGranted', PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE, false, false],
            ['canViewDemoTestRequestsLink', 'isGranted', PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE, true, true],
            ['canViewVehicleSearchLink', 'isGranted', PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW, false, false],
            ['canViewVehicleSearchLink', 'isGranted', PermissionInSystem::FULL_VEHICLE_MOT_TEST_HISTORY_VIEW, true, true],
            ['canViewDirectDebitLink', 'isGranted', PermissionInSystem::SLOTS_DIRECT_DEBIT_SEARCH, false, false],
            ['canViewDirectDebitLink', 'isGranted', PermissionInSystem::SLOTS_DIRECT_DEBIT_SEARCH, true, true],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewReplacementDuplicateCertificateLinkDataProvider()
    {
        return [
            [[RoleCode::USER], false, false],
            [[RoleCode::TESTER_ACTIVE], false, false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], false, true],
            [[RoleCode::TESTER], false, true],
            [[RoleCode::TESTER], true, false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], false, true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false, false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE], false, false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE,  RoleCode::TESTER], false, true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], false, true],
            [[RoleCode::CUSTOMER_SERVICE_OPERATIVE], false, true],
            [[RoleCode::DVLA_OPERATIVE], false, true],
            [[RoleCode::AREA_OFFICE_1], false, true],
            [[RoleCode::VEHICLE_EXAMINER], false, true],
            [[RoleCode::SCHEME_USER], false, true],
            [[RoleCode::SCHEME_MANAGER], false, true],
        ];
    }

    /**
     * @return array
     */
    public function userHasPermissionDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @return array
     */
    public function rolesWithViewPaymentLinkPermissionThatShouldNotSeePaymentLink()
    {
        return [
            [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER],
            [RoleCode::AUTHORISED_EXAMINER_DELEGATE]
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
