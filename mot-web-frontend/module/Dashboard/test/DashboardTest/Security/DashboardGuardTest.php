<?php

namespace DashboardTest\Security;

use Dashboard\Security\DashboardGuard;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class DashboardGuardTest extends PHPUnit_Framework_TestCase
{
    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject $mockAuthorisationService */
    protected $mockAuthorisationService;

    protected function setup()
    {
        $this->mockAuthorisationService = XMock::of(MotAuthorisationServiceInterface::class);
    }

    /**
     * @dataProvider testUserShouldDoDemoTestDataProvider
     *
     * @param string $userRole
     * @param bool   $expectedResult
     */
    public function testUserShouldDoDemoTest($userRole, $expectedResult)
    {
        $this->mockAuthorisationService
            ->method('hasRole')
            ->with($userRole)
            ->willReturn($expectedResult);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertEquals($expectedResult, $dashboardGuard->isDemoTestNeeded());
    }

    public function testUserHasPermissionToPerformDemoTest()
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::MOT_DEMO_TEST_PERFORM)
            ->willReturn(true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->canPerformDemoTest());
    }

    public function testUserHasPermissionToViewYourPerformance()
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::DISPLAY_TESTER_STATS_BOX)
            ->willReturn(true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->canViewYourPerformance());
    }

    public function testUserHasPermissionToViewContingencyTests()
    {
        $this->mockAuthorisationService
            ->method('isGranted')
            ->with(PermissionInSystem::DISPLAY_TESTER_CONTINGENCY_BOX)
            ->willReturn(true);

        $this->mockAuthorisationService
            ->method('hasRole')
            ->with(RoleCode::TESTER_ACTIVE)
            ->willReturn(true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->canViewContingencyTests());
    }

    /**
     * @return array
     */
    public function testUserShouldDoDemoTestDataProvider()
    {
        return [
            [RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, true],
            [RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED, false]
        ];
    }
}