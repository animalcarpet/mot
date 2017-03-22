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
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToViewAeAndVts($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::VEHICLE_TESTING_STATION_LIST, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewVehicleTestingStationList());
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
     * @dataProvider userCanViewReplacementDuplicateCertificateLink
     *
     * @param array $userRoles
     * @param bool  $hasPermission
     */
    public function testCanViewReplacementDuplicateCertificateLink(array $userRoles, $hasPermission)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewReplacementDuplicateCertificateLink());
    }

    /**
     * @dataProvider userCanViewSlotBalanceDataProvider
     *
     * @param array $userRoles
     * @param bool  $canViewSlotBalance
     */
    public function testUserCanViewSlotBalance(array $userRoles, $canViewSlotBalance)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($canViewSlotBalance, $dashboardGuard->canViewSlotBalance());
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToAcknowledgeSpecialNotices($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canAcknowledgeSpecialNotices());
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToReadAllSpecialNotices($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SPECIAL_NOTICE_READ, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canReadAllSpecialNotices());
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToReceiveSpecialNotices($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canReceiveSpecialNotices());
    }

    /**
     * @dataProvider isAreaOffice1DataProvider
     *
     * @param $userRole
     * @param $expectedResult
     */
    public function testUserHasAreaOffice1Role($userRole, $expectedResult)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRole);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($expectedResult , $dashboardGuard->isAreaOffice1());
    }

    /**
     * @dataProvider permissionToCreateAuthorisedExaminerDataProvider
     *
     * @param $permission
     * @param $expectedResult
     */
    public function testIfUserHasPermissionToCreateAuthorisedExaminer($permission, $expectedResult)
    {
        $this->addMethodToMockAuthorisationService('isGranted', $permission, $expectedResult);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($expectedResult , $dashboardGuard->canCreateAuthorisedExaminer());
    }

    /**
     * @dataProvider permissionToCreateVehicleTestingStationDataProvider
     *
     * @param $userPermission
     * @param $expectedResult
     */
    public function testUsesHasPermissionToCreateVehicleTestingStation($userPermission, $expectedResult)
    {
        $this->addMethodToMockAuthorisationService('isGranted', $userPermission , $expectedResult);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($expectedResult, $dashboardGuard->canCreateVehicleTestingStation());
    }

    public function permissionToCreateAuthorisedExaminerDataProvider()
    {
        return [
            [PermissionInSystem::AUTHORISED_EXAMINER_CREATE, true, true],
            [null, false, false]
        ];
    }

    public function permissionToCreateVehicleTestingStationDataProvider()
    {
        return [
            [PermissionInSystem::VEHICLE_TESTING_STATION_CREATE, true, true],
            [null, false, false]
        ];
    }

    /**
     * @return array
     */
    public function isAreaOffice1DataProvider()
    {
        return [
            [[RoleCode::USER], false],
            [[RoleCode::AREA_OFFICE_1], true],
            [[RoleCode::USER, RoleCode::AREA_OFFICE_1], true]
        ];
    }

    /**
     * @dataProvider userShouldDoDemoTestDataProvider
     *
     * @param array $userRoles
     * @param bool  $shouldDoDemoTest
     */
    public function testUserShouldDoDemoTest(array $userRoles, $shouldDoDemoTest)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($shouldDoDemoTest, $dashboardGuard->isDemoTestNeeded());
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
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToViewYourPerformance($hasPermission)
    {
        $this->addMethodToMockAuthorisationService('isGranted', PermissionInSystem::DISPLAY_TESTER_STATS_BOX, $hasPermission);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewYourPerformance());
    }

    /**
     * @dataProvider userHasPermissionToViewContingencyTestsDataProvider
     *
     * @param array $userRoles
     * @param bool  $canViewContingencyTests
     */
    public function testUserHasPermissionToViewContingencyTests(array $userRoles, $canViewContingencyTests)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($canViewContingencyTests, $dashboardGuard->isQualifiedTester());
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
     * @dataProvider userIsTesterDataProvider
     *
     * @param array $userRoles
     * @param bool  $isTester
     */
    public function testUserIsTester(array $userRoles, $isTester)
    {
        $this->addMethodToMockAuthorisationService('getAllRoles', null, $userRoles);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($isTester, $dashboardGuard->isTester());
    }

    /**
     * @dataProvider userCanViewLinkMethodsDataProvider
     *
     * @param string $testMethod
     * @param string $mockMethod
     * @param string $mockPermission
     * @param bool   $mockResult
     * @param string $testResult
     */
    public function testUserCanViewLinkMethodsInDashboardGuard(
        $testMethod,
        $mockMethod,
        $mockPermission,
        $mockResult,
        $testResult)
    {
        $this->addMethodToMockAuthorisationService($mockMethod, $mockPermission, $mockResult);
        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($dashboardGuard->$testMethod(), $testResult);
    }

    /**
     * @return array
     */
    public function userShouldDoDemoTestDataProvider()
    {
        return [
            [[RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            [[RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            [[RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            [[RoleCode::TESTER, RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false],
        ];
    }

    /**
     * @return array
     */
    public function userCanViewSlotBalanceDataProvider()
    {
        return [
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
            [[RoleCode::AUTHORISED_EXAMINER_DELEGATE], true],
            [[RoleCode::SITE_ADMIN], true],
            [[RoleCode::SITE_MANAGER], true],
            [[RoleCode::TESTER], false],
            [[RoleCode::USER], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], true],
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
        ];
    }

    /**
     * @return array
     */
    public function userCanViewReplacementDuplicateCertificateLink()
    {
        return [
            [[RoleCode::USER], false],
            [[RoleCode::TESTER_ACTIVE], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
            [[RoleCode::TESTER], true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE], false],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE,  RoleCode::TESTER], true],
            [[RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], true],
            [[RoleCode::CUSTOMER_SERVICE_OPERATIVE], true],
            [[RoleCode::DVLA_OPERATIVE], true],
            [[RoleCode::AREA_OFFICE_1], true],
        ];
    }

    /**
     * @return array
     */
    public function userIsTesterDataProvider()
    {
        return [
            [[RoleCode::TESTER], true],
            [[RoleCode::AUTHORISED_EXAMINER_DELEGATE], false],
        ];
    }

    /**
     * @param $methodName
     * @param $withParameter
     * @param $returnValue
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
