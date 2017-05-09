<?php

namespace Dashboard\ViewModel;

use Dashboard\Model\SpecialNotice;
use Dashboard\Security\DashboardGuard;
use DvsaCommonTest\TestUtils\XMock;

class SpecialNoticeViewModelTest extends \PHPUnit_Framework_TestCase
{
    private $mockSpecialNotice;
    private $mockDashboardGuard;

    public function setup()
    {
        $this->mockSpecialNotice = XMock::of(SpecialNotice::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
    }

    public function testIfSpecialNoticeIsVisible()
    {
        $this->mockDashboardGuard
            ->method('canReceiveSpecialNotices')
            ->willReturn(true);

        $specialNotices = new SpecialNoticesViewModel(0, 0, 0, $this->mockDashboardGuard);

        $this->assertEquals(true, $specialNotices->isVisible());
    }

    public function testShouldReturnTrueForDaysLeftToView()
    {
        $this->mockCanAcknowledgeSpecialNotices(true);

        $specialNotices = new SpecialNoticesViewModel(1, 0, 0, $this->mockDashboardGuard);

        $this->assertEquals(true, $specialNotices->isDaysLeftToViewVisible());
    }

    /**
     * @dataProvider specialNoticesUrlDataProvider
     *
     * @param string $expectedURL
     * @param bool   $canUserReadAllSpecialNotices
     */
    public function testValidUrlBasedOnUserPermission($expectedURL, $canUserReadAllSpecialNotices)
    {
        $this->mockCanReadAllSpecialNotices($canUserReadAllSpecialNotices);

        $specialNotices = new SpecialNoticesViewModel(0, 0, 0, $this->mockDashboardGuard);

        $this->assertEquals($expectedURL, $specialNotices->getUrl());
    }

    /**
     * @dataProvider canAcknowledgeDataProvider
     *
     * @param bool $canAcknowledge
     * @param bool $expectedResult
     */
    public function testIfUserCanAcknowledgeSpecialNotices($canAcknowledge, $expectedResult)
    {
        $this->mockCanAcknowledgeSpecialNotices($canAcknowledge);

        $specialNotices = new SpecialNoticesViewModel(0, 0, 0, $this->mockDashboardGuard);

        $this->assertEquals($expectedResult, $specialNotices->canAcknowledge());
    }

    /**
     * @dataProvider overdueSpecialNoticeDataProvider
     *
     * @param bool $canAcknowledge
     * @param int  $unreadCount
     * @param int  $overdueCount
     * @param int  $daysLeftToView
     * @param bool $expectedResult
     */
    public function testOverdueSpecialNotices(
        $canAcknowledge,
        $unreadCount,
        $overdueCount,
        $daysLeftToView,
        $expectedResult
    ) {
        $specialNotices = new SpecialNoticesViewModel(
            $unreadCount,
            $overdueCount,
            $daysLeftToView,
            $this->mockDashboardGuard
        );

        $this->assertTrue(true, $this->mockCanAcknowledgeSpecialNotices($canAcknowledge));
        $this->assertEquals($expectedResult, $specialNotices->isOverdue());
    }

    /**
     * @dataProvider specialNoticeViewModelDataProvider
     *
     * @param int $unreadCount
     * @param int $overdueCount
     * @param int $daysLeftToView
     * @param int $expectedNumberOfNotices
     */
    public function testIfGetOverdueCountNoticesReturnsCorrectNumberOfOverdueCount(
        $unreadCount,
        $overdueCount,
        $daysLeftToView,
        $expectedNumberOfNotices
    ) {
        $specialNotices = new SpecialNoticesViewModel(
            $unreadCount,
            $overdueCount,
            $daysLeftToView,
            $this->mockDashboardGuard
        );

        $this->assertEquals($expectedNumberOfNotices, $specialNotices->getNumberOfOverdueSpecialNotices());
    }

    /**
     * @param $returnValue
     *
     * @return mixed
     */
    private function mockCanAcknowledgeSpecialNotices($returnValue)
    {
        return $this->mockDashboardGuard
            ->method('canAcknowledgeSpecialNotices')
            ->willReturn($returnValue);
    }

    /**
     * @param $returnValue
     *
     * @return mixed
     */
    private function mockCanReadAllSpecialNotices($returnValue)
    {
        return $this->mockDashboardGuard
            ->method('canReadAllSpecialNotices')
            ->willReturn($returnValue);
    }

    /**
     * @return array
     */
    public function specialNoticeViewModelDataProvider()
    {
        return [
            [
                0, 10, 0, 10,
            ],
            [
                10, 0, 0, 0,
            ],
        ];
    }

    /**
     * @return array
     */
    public function overdueSpecialNoticeDataProvider()
    {
        return [
            [
                true, 0, 1, 0, true,
            ],
            [
                false, 0, 10, 0, false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function canAcknowledgeDataProvider()
    {
        return [
            [
                true, true,
            ],
            [
                false, false,
            ],
        ];
    }

    /**
     * @return array
     */
    public function specialNoticesUrlDataProvider()
    {
        return [
            [
                'special-notices/all', true,
            ],
            [
                'special-notices', false,
            ],
        ];
    }
}
