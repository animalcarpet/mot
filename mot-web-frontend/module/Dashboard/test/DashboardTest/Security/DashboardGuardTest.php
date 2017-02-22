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
    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockAuthorisationService;

    protected function setup()
    {
        $this->mockAuthorisationService = XMock::of(MotAuthorisationServiceInterface::class);
    }

    public function testUserShouldDoDemoTest()
    {
        $this->mockAuthorisationService->method('hasRole')
            ->withConsecutive([RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED],
                              [RoleCode::TESTER_ACTIVE])
            ->willReturnOnConsecutiveCalls(true, false);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->isDemoTestNeeded());
    }

    public function testUserShouldNotDoDemoTest()
    {
        $this->mockAuthorisationService->method('hasRole')
            ->withConsecutive([RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED],
                              [RoleCode::TESTER_ACTIVE])
            ->willReturnOnConsecutiveCalls(true, true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertFalse($dashboardGuard->isDemoTestNeeded());
    }

    public function testUserHasPermissionToPerformDemoTest()
    {
        $this->mockAuthorisationService->method('isGranted')
            ->with(PermissionInSystem::MOT_DEMO_TEST_PERFORM)
            ->willReturn(true);

        $dashboardGuard = new DashboardGuard($this->mockAuthorisationService);

        $this->assertTrue($dashboardGuard->canPerformDemoTest());
    }
}