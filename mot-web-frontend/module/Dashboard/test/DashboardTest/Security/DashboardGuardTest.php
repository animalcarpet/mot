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
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::VEHICLE_TESTING_STATION_LIST)
            ->willReturn($hasPermission);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewVehicleTestingStationList());
    }

    /**
     * @return array
     */
    public function userHasPermissionDataProvider()
    {
        return [
            [ true ],
            [ false ]
        ];
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserIsAssignedToAndCanViewVts($hasPermission)
    {
        $siteId = 1;

        $this->mockAuthorisationService
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::VEHICLE_TESTING_STATION_READ)
            ->willReturn($hasPermission);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewVehicleTestingStation($siteId));
    }

    /**
     * @dataProvider userShouldViewReplacementDuplicateCertificateLinkDataProvider
     *
     * @param array  $userRoles
     * @param bool   $hasPermission
     */
    public function testUserShouldViewReplacementDuplicateCertificateLink(array $userRoles, $hasPermission)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($userRoles);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewReplacementDuplicateCertificateLink());
    }

    /**
     * @return array
     */
    public function userShouldViewReplacementDuplicateCertificateLinkDataProvider()
    {
        return [
            [ [RoleCode::USER], false ],
            [ [RoleCode::TESTER_ACTIVE], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true ],
            [ [RoleCode::TESTER], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, RoleCode::TESTER_ACTIVE,  RoleCode::TESTER], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], true ],
        ];
    }

    /**
     * @dataProvider userCanViewSlotBalanceDataProvider
     *
     * @param array $userRoles
     * @param bool  $canViewSlotBalance
     */
    public function testUserCanViewSlotBalance(array $userRoles, $canViewSlotBalance)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($userRoles);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($canViewSlotBalance, $dashboardGuard->canViewSlotBalance());
    }

    /**
     * @return array
     */
    public function userCanViewSlotBalanceDataProvider()
    {
        return [
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DELEGATE], true ],
            [ [RoleCode::SITE_ADMIN], true ],
            [ [RoleCode::SITE_MANAGER], true ],
            [ [RoleCode::TESTER], false ],
            [ [RoleCode::USER], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER_ACTIVE], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE], false ],
            [ [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, RoleCode::TESTER], true ],
        ];
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToAcknowledgeSpecialNotices($hasPermission)
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE)
            ->willReturn($hasPermission);

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
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::SPECIAL_NOTICE_READ)
            ->willReturn($hasPermission);

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
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT)
            ->willReturn($hasPermission);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canReceiveSpecialNotices());
    }

    /**
     * @dataProvider userShouldDoDemoTestDataProvider
     *
     * @param array $userRoles
     * @param bool  $shouldDoDemoTest
     */
    public function testUserShouldDoDemoTest(array $userRoles, $shouldDoDemoTest)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($userRoles);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($shouldDoDemoTest, $dashboardGuard->isDemoTestNeeded());
    }

    /**
     * @return array
     */
    public function userShouldDoDemoTestDataProvider()
    {
        return [
            [ [RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true ],
            [ [RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true ],
            [ [RoleCode::TESTER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true ],
            [ [RoleCode::TESTER, RoleCode::TESTER_ACTIVE, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], false ]
        ];
    }

    /**
     * @dataProvider testUserCanPerformMotTestDataProvider
     *
     * @param bool $hasMotTestStartPermission
     * @param bool $hasNoOverdueSpecialNotices
     * @param bool $userShouldHavePermissionToPerformMotTest
     */
    public function testUserCanPerformMotTest(
        $hasMotTestStartPermission,
        $hasNoOverdueSpecialNotices,
        $userShouldHavePermissionToPerformMotTest
    )
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::MOT_TEST_START)
            ->willReturn($hasMotTestStartPermission);

        $this->mockOverdueSpecialNoticeAssertion
            ->method('canPerformTest')
            ->willReturn($hasNoOverdueSpecialNotices);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);
        $dashboardGuard->setOverdueSpecialNoticeAssertion($this->mockOverdueSpecialNoticeAssertion);

        $this->assertEquals($userShouldHavePermissionToPerformMotTest, $dashboardGuard->isTestingEnabled());
    }

    public function testUserCanPerformMotTestDataProvider()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false]
        ];
    }

    /**
     * @dataProvider userIsTesterDataProvider
     *
     * @param array $userRoles
     * @param bool  $canViewSlotBalance
     */
    public function testUserIsTester(array $userRoles, $canViewSlotBalance)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($userRoles);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($canViewSlotBalance, $dashboardGuard->isTester());
    }

    /**
     * @return array
     */
    public function userIsTesterDataProvider()
    {
        return [
            [ [RoleCode::TESTER], true ],
            [ [RoleCode::AUTHORISED_EXAMINER_DELEGATE], false ]
        ];
    }

    /**
     * @dataProvider userHasPermissionDataProvider
     *
     * @param bool $hasPermission
     */
    public function testUserHasPermissionToViewYourPerformance($hasPermission)
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::DISPLAY_TESTER_STATS_BOX)
            ->willReturn($hasPermission);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($hasPermission, $dashboardGuard->canViewYourPerformance());
    }

    /**
     * @dataProvider userHasPermissionToViewContingencyTestsDataProvider
     *
     * @param array $userRoles
     * @param bool $canViewContingencyTests
     */
    public function testUserHasPermissionToViewContingencyTests(array $userRoles, $canViewContingencyTests)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($userRoles);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($canViewContingencyTests, $dashboardGuard->isQualifiedTester());
    }

    /**
     * @return array
     */
    public function userHasPermissionToViewContingencyTestsDataProvider()
    {
        return [
            [ [RoleCode::TESTER_ACTIVE, RoleCode::TESTER], true ],
            [ [RoleCode::TESTER], false ],
        ];
    }
}
