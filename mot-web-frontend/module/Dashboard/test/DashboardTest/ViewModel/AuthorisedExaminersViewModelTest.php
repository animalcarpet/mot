<?php

namespace DashboardTest\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\AuthorisedExaminersViewModel;
use Dashboard\ViewModel\AuthorisedExaminerViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class AuthorisedExaminersViewModelTest extends PHPUnit_Framework_TestCase
{
    /** @var DashboardGuard | \PHPUnit_Framework_MockObject_MockObject */
    private $dashboardGuardMock;

    /** @var AuthorisedExaminerViewModel | \PHPUnit_Framework_MockObject_MockObject  */
    private $authorisedExaminerViewModelMock;

    public function setup() {
        $this->dashboardGuardMock = XMock::of(DashboardGuard::class);
        $this->authorisedExaminerViewModelMock = XMock::of(AuthorisedExaminerViewModel::class);
    }

    /**
     * @dataProvider testIfUserCanViewAEAndVtsDataProvider
     *
     * @param bool $canViewVehicleTestingStationList
     * @param bool $expectedResult
     */
    public function testIfUserCanViewAEAndVts($canViewVehicleTestingStationList, $expectedResult)
    {
        $this->dashboardGuardMock
            ->method('canViewVehicleTestingStationList')
            ->willReturn($canViewVehicleTestingStationList);

        $authorisedExaminersViewModel = new AuthorisedExaminersViewModel(
            $this->dashboardGuardMock,
            [$this->authorisedExaminerViewModelMock, $this->authorisedExaminerViewModelMock]
        );

        $this->assertEquals($expectedResult, $authorisedExaminersViewModel->isVisible());
    }

    public function testAuthorisedExaminersAreIterable()
    {
        $this->dashboardGuardMock
            ->method('canViewVehicleTestingStationList')
            ->willReturn(true);

        $authorisedExaminersViewModel = new AuthorisedExaminersViewModel(
            $this->dashboardGuardMock,
            [$this->authorisedExaminerViewModelMock, $this->authorisedExaminerViewModelMock]
        );

        foreach ($authorisedExaminersViewModel as $authorisedExaminerViewModel) {
            $this->assertInstanceOf(AuthorisedExaminerViewModel::class, $authorisedExaminerViewModel);
        }
    }

    /**
     * @return array
     */
    public function testIfUserCanViewAEAndVtsDataProvider()
    {
        return [
            [
                true,
                true,
            ],
            [
                false,
                false,
            ],
        ];
    }
}
