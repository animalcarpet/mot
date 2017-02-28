<?php

namespace DashboardTest\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\Model\Dashboard;
use Dashboard\ViewModel\HeroActionViewModel;
use Dashboard\ViewModel\SlotsViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class HeroActionViewModelTest extends PHPUnit_Framework_TestCase
{
    const USER_ROLE = 'user';
    const AEDM_ROLE = 'aedm';

    /** @var Dashboard $mockDashboard */
    private $mockDashboard;

    /** @var DashboardGuard $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var SlotsViewModel $mockSlotsViewModel */
    private $mockSlotsViewModel;

    public function setup()
    {
        $this->mockDashboard = XMock::of(Dashboard::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockSlotsViewModel = XMock::of(SlotsViewModel::class);
    }

    public function testEmptyHeroAction()
    {
        $heroAction = $this->createHeroActionViewModel("");

        $this->assertFalse($heroAction->isHeroActionVisible());
    }

    public function testIfHeroActionIsNotVisibleForUserWithUserRoleOnly()
    {
        $heroAction = $this->createHeroActionViewModel(self::USER_ROLE);

        $this->assertFalse($heroAction->isHeroActionVisible());
    }

    public function testIfHeroActionIsVisibleForAedmRole()
    {
        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue($heroAction->isHeroActionVisible());
    }

    public function testIfSlotCountIsVisibleForAedmRole()
    {
        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue($heroAction->isHeroActionVisible());
        $this->assertTrue($heroAction->isSlotCountVisible());
    }

    public function testIfSlotCountIsNotVisibleForUserWithUserRoleOnly()
    {
        $heroAction = $this->createHeroActionViewModel(self::USER_ROLE);

        $this->assertTrue(true, $heroAction->isHeroActionVisible());
        $this->assertFalse($heroAction->isSlotCountVisible());
    }

    public function testIfSlotCountReturnExpectedSlotCount()
    {
        $this->mockSlotsViewModel
            ->method('getOverallSlotCount')
            ->willReturn(100);

        $expectedNumberOfSlots = 100;

        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue(true, $heroAction->isHeroActionVisible());
        $this->assertEquals($expectedNumberOfSlots, $heroAction->getOverallSlotCount());
    }

    public function testIfReplacementDuplicateCertificateLinkIsVisibleForAemdRole()
    {
        $this->mockSlotsViewModel
             ->method('isSlotCountVisible')
             ->willReturn(true);

        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue(true, $heroAction->isHeroActionVisible());
        $this->assertTrue($heroAction->isSlotCountVisible());
    }

    public function testIfReplacementDuplicateCertificateLinkIsNotVisibleForUserWithUserRoleOnly()
    {
        $heroAction = $this->createHeroActionViewModel(self::USER_ROLE);

        $this->assertTrue(true, $heroAction->isHeroActionVisible());
        $this->assertFalse($heroAction->isSlotCountVisible());
    }

    public function testIfSiteCountIsVisibleForAedmRole()
    {
        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue($heroAction->isHeroActionVisible());
        $this->assertTrue($heroAction->isSiteCountVisible());
    }

    public function testIfOverallSiteCountReturnExpectedSiteCountAmount()
    {
        $this->mockSlotsViewModel
            ->method('getOverallSiteCount')
            ->willReturn(10);

        $expectedNumberOfSites = 10;

        $heroAction = $this->createHeroActionViewModel(self::AEDM_ROLE);

        $this->assertTrue(true, $heroAction->isHeroActionVisible());
        $this->assertEquals($expectedNumberOfSites, $heroAction->getOverallSiteCount());
    }

    /**
     * @param string $userRole
     *
     * @return HeroActionViewModel
     */
    private function createHeroActionViewModel($userRole)
    {
        return new HeroActionViewModel($userRole, $this->mockSlotsViewModel, $this->mockDashboardGuard);
    }
}
