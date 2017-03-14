<?php

namespace DashboardTest\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\HeroActionViewModel;
use Dashboard\ViewModel\ReplacementDuplicateCertificateViewModel;
use Dashboard\ViewModel\SlotsViewModel;
use Dashboard\ViewModel\StartMotViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class HeroActionViewModelTest extends PHPUnit_Framework_TestCase
{
    /** @var SlotsViewModel $mockSlotsViewModel */
    private $mockSlotsViewModel;

    /** @var DashboardGuard $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var  StartMotViewModel $mockStartMotViewModel */
    private $mockStartMotViewModel;

    /** @var  ReplacementDuplicateCertificateViewModel $mockRdCertificateViewModel */
    private $mockRdCertificateViewModel;

    public function setup()
    {
        $this->mockSlotsViewModel = XMock::of(SlotsViewModel::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockStartMotViewModel = XMock::of(StartMotViewModel::class);
        $this->mockRdCertificateViewModel = XMock::of(ReplacementDuplicateCertificateViewModel::class);
    }

    public function testEmptyHeroAction()
    {
        $heroAction = $this->heroActionViewModel();

        $this->assertFalse($heroAction->isHeroActionVisible());
    }

    /**
     * @dataProvider canViewHeroActionDataProvider
     *
     * @param bool $canViewRdCertificateLink
     * @param bool $canViewSlotsBalance
     * @param bool $canStartMotTest
     * @param bool $isMotFormsLinkVisible
     * @param bool $expectedResult
     */
    public function testIfHeroActionIsVisible(
        $canViewRdCertificateLink,
        $canViewSlotsBalance,
        $canStartMotTest,
        $isMotFormsLinkVisible,
        $expectedResult)
    {
        $this->mockRdCertificateViewModel
            ->method('canViewReplacementDuplicateCertificateLink')
            ->willReturn($canViewRdCertificateLink);

        $this->mockSlotsViewModel
            ->method('canViewSlotBalance')
            ->willReturn($canViewSlotsBalance);

        $this->mockStartMotViewModel
            ->method('canStartMotTest')
            ->willReturn($canStartMotTest);

        $this->mockDashboardGuard
            ->method('isTester')
            ->willReturn($isMotFormsLinkVisible);

        $heroAction = $this->heroActionViewModel();

        $this->assertEquals($expectedResult, $heroAction->isHeroActionVisible());
    }

    /**
     * @return array
     */
    public function canViewHeroActionDataProvider()
    {
        return [
            [ true, true, true, true, true ],
            [ false, true, true, true, true ],
            [ false, false, true, true, true ],
            [ false, false, false, true, true ],
            [ false, false, false, false, false ],
        ];
    }

    /**
     * @return HeroActionViewModel
     */
    private function heroActionViewModel()
    {
        return new HeroActionViewModel(
            $this->mockDashboardGuard,
            $this->mockSlotsViewModel,
            $this->mockRdCertificateViewModel,
            $this->mockStartMotViewModel
        );
    }
}
