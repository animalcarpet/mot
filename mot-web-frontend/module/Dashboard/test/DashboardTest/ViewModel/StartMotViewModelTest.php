<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\Model\Dashboard;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class StartMotViewModelTest extends \PHPUnit_Framework_TestCase
{
    private $mockDashboard;
    private $mockDashboardGuard;
    private $mockVehicleTestingStation;

    public function setup()
    {
        $this->mockDashboard = XMock::of(Dashboard::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockVehicleTestingStation = XMock::of(VehicleTestingStation::class);
    }

    /**
     * @dataProvider statMotViewModelDataProviderForHasSlotsAvailable
     *
     * @param bool $isTesterAtAnySite
     * @param bool $hasTestInProgress
     * @param string $enterResultsLabel
     * @param int $testNumberInProgress
     * @param bool $isTestingEnabled
     * @param int $slotsNumber
     * @param bool $expectedResult
     */
    public function testIfTesterHasSlotsAvailable(
        $isTesterAtAnySite,
        $hasTestInProgress,
        $enterResultsLabel,
        $testNumberInProgress,
        $isTestingEnabled,
        $slotsNumber,
        $expectedResult
    )
    {
        $this->mockVehicleTestingStation
            ->method('getSlots')
            ->willReturn($slotsNumber);

        $startMotViewModel = $this->startMotViewModel(
            $isTesterAtAnySite,
            $hasTestInProgress,
            $enterResultsLabel,
            $testNumberInProgress,
            $isTestingEnabled,
            $this->mockVehicleTestingStation,
            $expectedResult
        );

        $this->assertEquals($expectedResult, $startMotViewModel->hasSlotsAvailable());
    }

    /**
     * @dataProvider statMotViewModelDataProviderForCanPerformMotTest
     *
     * @param bool $isTesterAtAnySite
     * @param bool $hasTestInProgress
     * @param string $enterResultsLabel
     * @param int $testNumberInProgress
     * @param bool $isTestingEnabled
     * @param bool $expectedResult
     */
    public function testIfUserCanStartMotTest(
        $isTesterAtAnySite,
        $hasTestInProgress,
        $enterResultsLabel,
        $testNumberInProgress,
        $isTestingEnabled,
        $expectedResult
    )
    {
        $startMotViewModel = $this->startMotViewModel(
            $isTesterAtAnySite,
            $hasTestInProgress,
            $enterResultsLabel,
            $testNumberInProgress,
            $isTestingEnabled,
            $this->mockVehicleTestingStation,
            $expectedResult
        );

        $this->assertEquals($expectedResult, $startMotViewModel->canStartMotTest());
    }

    /**
     * @return array
     */
    public function statMotViewModelDataProviderForHasSlotsAvailable()
    {
        return [
            [true, true, "Enter MOT", 999, true, 0, false],
            [true, true, "Enter MOT", 999, true, 1, true]
        ];
    }

    /**
     * @return array
     */
    public function statMotViewModelDataProviderForCanPerformMotTest()
    {
        return [
            [true,  true, "Enter MOT", 999, true,  true],
            [true,  true, "Enter MOT", 999, false, false],
            [false, true, "Enter MOT", 999, true,  false],
            [false, true, "Enter MOT", 999, false, false]
        ];
    }

    private function startMotViewModel(
        $isTesterAtAnySite,
        $hasTestInProgress,
        $enterResultsLabel,
        $testNumberInProgress,
        $isTestingEnabled,
        $testerAtCurrentVts,
        $expectedResult
    )
    {
        $startMotViewModel = new StartMotViewModel(
            $isTesterAtAnySite,
            $hasTestInProgress,
            $enterResultsLabel,
            $testNumberInProgress,
            $isTestingEnabled,
            $testerAtCurrentVts,
            $expectedResult);

        return $startMotViewModel;
    }

}
