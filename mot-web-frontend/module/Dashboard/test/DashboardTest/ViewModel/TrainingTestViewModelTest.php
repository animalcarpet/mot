<?php

namespace DashboardTest\ViewModel;

use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\TrainingTestViewModel;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;

class TrainingTestViewModelTest extends PHPUnit_Framework_TestCase
{
    /** @var DashboardGuard | \PHPUnit_Framework_MockObject_MockObject */
    private $mockDashboardGuard;

    public function setup() {
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
    }

    public function testViewModelWithoutInProgressTestNumber()
    {
        $viewModel = new TrainingTestViewModel(
            $this->mockDashboardGuard
        );

        $this->assertEquals('action-start-mot-demonstration', $viewModel->getLinkId());
        $this->assertEquals('Start training test', $viewModel->getLinkViewModel()->getText());
        $this->assertEquals(VehicleUrlBuilder::TRAINING_SEARCH, $viewModel->getLinkViewModel()->getHref());
    }

    public function testViewModelWithInProgressTestNumber()
    {
        $viewModel = new TrainingTestViewModel(
            $this->mockDashboardGuard
        );
        $viewModel->setInProgressTestNumber('1010101');

        $this->assertEquals('action-resume-mot-demonstration', $viewModel->getLinkId());
        $this->assertEquals('Resume training test', $viewModel->getLinkViewModel()->getText());
        $this->assertEquals('mot-test/1010101', $viewModel->getLinkViewModel()->getHref());
    }
}
