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
    /** @var DashboardGuard | \PHPUnit_Framework_MockObject_MockObject $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var SlotsViewModel | \PHPUnit_Framework_MockObject_MockObject $mockSlotsViewModel */
    private $mockSlotsViewModel;

    /** @var ReplacementDuplicateCertificateViewModel | \PHPUnit_Framework_MockObject_MockObject $mockReplacementDuplicateCertificateViewModel */
    private $mockReplacementDuplicateCertificateViewModel;

    /** @var StartMotViewModel | \PHPUnit_Framework_MockObject_MockObject $mockStartMotViewModel */
    private $mockStartMotViewModel;

    public function setup()
    {
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockSlotsViewModel = XMock::of(SlotsViewModel::class);
        $this->mockReplacementDuplicateCertificateViewModel = XMock::of(ReplacementDuplicateCertificateViewModel::class);
        $this->mockStartMotViewModel = XMock::of(StartMotViewModel::class);
    }

    public function testEmptyHeroAction()
    {
        $heroAction = $this->heroActionViewModel();

        $this->assertFalse($heroAction->isHeroActionVisible());
    }

    /**
     * @dataProvider heroActionIsVisibleIfAnyOneChildIsVisibleDataProvider
     *
     * @param string $methodUnderTest
     * @param bool   $expectedResult
     */
    public function testHeroActionIsVisibleIfAnyOneChildIsVisible($methodUnderTest, $expectedResult)
    {
        $heroActionChildrenVisibilityMethods = [
            'canStartMotTest' => $this->mockStartMotViewModel,
            'canViewSlotBalance' => $this->mockSlotsViewModel,
            'canViewAeInformationLink' => $this->mockDashboardGuard,
            'canViewSiteInformationLink' => $this->mockDashboardGuard,
            'canViewUserSearchLink' => $this->mockDashboardGuard,
            'canViewReplacementDuplicateCertificateLink' => $this->mockReplacementDuplicateCertificateViewModel,
            'canViewMotFormsLink' => $this->mockDashboardGuard,
            'canViewDemoTestRequestsLink' => $this->mockDashboardGuard,
        ];

        foreach ($heroActionChildrenVisibilityMethods as $method => $mock) {
            if ($method == $methodUnderTest) {
                $mock->method($method)->willReturn(true);
            } else {
                $mock->method($method)->willReturn(false);
            }
        }

        $heroAction = $this->heroActionViewModel();

        $this->assertEquals($heroAction->isHeroActionVisible(), $expectedResult);
    }

    /**
     * @return array
     */
    public function heroActionIsVisibleIfAnyOneChildIsVisibleDataProvider()
    {
        return [
            ['not visible test case', false],
            ['canStartMotTest', true],
            ['canViewSlotBalance', true],
            ['canViewAeInformationLink', true],
            ['canViewSiteInformationLink', true],
            ['canViewUserSearchLink', true],
            ['canViewReplacementDuplicateCertificateLink', true],
            ['canViewMotFormsLink', true],
            ['canViewDemoTestRequestsLink', true],
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
            $this->mockReplacementDuplicateCertificateViewModel,
            $this->mockStartMotViewModel
        );
    }
}
