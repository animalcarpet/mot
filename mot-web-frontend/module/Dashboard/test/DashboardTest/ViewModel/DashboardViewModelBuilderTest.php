<?php

namespace DashboardTest\ViewModel;

use Dashboard\Model\Dashboard;
use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\DashboardViewModel;
use Dashboard\ViewModel\DashboardViewModelBuilder;
use Dashboard\ViewModel\LinkViewModel;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\Plugin\Url;

class DashboardViewModelBuilderTest extends PHPUnit_Framework_TestCase
{
    /** @var array $dashboardData */
    private $dashboardData;

    /** @var AuthorisationServiceMock $authorisationServiceMock */
    private $authorisationServiceMock;

    /** @var DashboardViewModel|\PHPUnit_Framework_MockObject_MockObject $dashboardViewModel */
    private $mockDashboardViewModel;

    /** @var DashboardGuard|\PHPUnit_Framework_MockObject_MockObject $mockDashboardGuard */
    private $mockDashboardGuard;

    /** @var Url|\PHPUnit_Framework_MockObject_MockObject $mockUrl */
    private $mockUrl;

    /** @var Dashboard|\PHPUnit_Framework_MockObject_MockObject $mockDashboardGuard */
    private $mockDashboard;

    /** @var MotFrontendIdentityInterface|\PHPUnit_Framework_MockObject_MockObject $mockMotFrontendIdentityInterface */
    private $mockMotFrontendIdentityInterface;

    public function setUp()
    {
        $this->dashboardData = [];
        $this->authorisationServiceMock = new AuthorisationServiceMock();
        $this->mockDashboardViewModel = XMock::of(DashboardViewModel::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
        $this->mockUrl = XMock::of(Url::class);
        $this->mockDashboard = XMock::of(Dashboard::class);
        $this->mockMotFrontendIdentityInterface = XMock::of(MotFrontendIdentityInterface::class);
    }

    public function testShouldReturnANewDashboardViewModel()
    {
        $this->mockDashboardViewModel
            ->method('setShowDemoMessage')
            ->willReturn(true);

        $this->mockDashboardViewModel
            ->method('setShowYourPerformance')
            ->willReturn(true);

        $this->mockDashboardViewModel
            ->method('setShowContingencyTests')
            ->willReturn(true);

        $dashboardViewModelBuilder = $this->buildDashboardViewModelBuilder();

        $dashboardViewModel = $dashboardViewModelBuilder->build();

        $this->assertObjectHasAttribute("heroActionViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("notificationsViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("trainingTestViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("nonMotTestViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("authorisedExaminersViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("specialNoticesViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("authorisedExaminerManagementViewModel", $dashboardViewModel);
    }

    public function testInProgressTrainingTestNumberIsPassedToTrainingTestViewModel()
    {
        $inProgressTrainingTestNumber = '989978';

        $this->dashboardData = ['inProgressDemoTestNumber' => $inProgressTrainingTestNumber];

        $this->mockUrl
            ->method('fromRoute')
            ->willReturn('mot-test/'.$inProgressTrainingTestNumber);

        $trainingTestViewModel = $this->buildDashboardViewModelBuilder()->build()->getTrainingTestViewModel();

        $this->assertLinkHrefEndsWithDemoTestNumber($inProgressTrainingTestNumber, $trainingTestViewModel->getLinkViewModel());
    }

    /**
     * @dataProvider shouldShowContingencyTestsDataProvider
     *
     * @param bool $isTesterAtAnySite
     * @param bool $isTestingEnabled
     * @param bool $expectedResult
     */
    public function testShouldShowContingencyTests($isTesterAtAnySite, $isTestingEnabled, $expectedResult)
    {
        $this->mockDashboard
            ->method('isTesterAtAnySite')
            ->willReturn($isTesterAtAnySite);

        $this->mockDashboardGuard
            ->method('isTestingEnabled')
            ->willReturn($isTestingEnabled);

        $dashboardViewModelBuilder = new DashboardViewModelBuilder(
            $this->mockMotFrontendIdentityInterface,
            $this->mockDashboard,
            $this->mockDashboardGuard,
            $this->mockUrl
        );

        $this->assertEquals($expectedResult, $dashboardViewModelBuilder->shouldShowContingencyTests());
    }

    /**
     * @param int           $trainingTestNumber
     * @param LinkViewModel $linkViewModel
     */
    private function assertLinkHrefEndsWithDemoTestNumber($trainingTestNumber, LinkViewModel $linkViewModel)
    {
        $this->assertRegExp("/$trainingTestNumber\$/", $linkViewModel->getHref());
    }

    /**
     * @return DashboardViewModelBuilder
     *
     * @throws \Exception
     */
    private function buildDashboardViewModelBuilder()
    {
        $dashboardDataDefaults = [
            'hero' => '',
            'authorisedExaminers' => [],
            'specialNotice' => [
                'daysLeftToView' => 0,
                'unreadCount' => 0,
                'overdueCount' => 0,
            ],
            'overdueSpecialNotices' => [],
            'notifications' => [],
            'inProgressTestNumber' => '',
            'inProgressTestTypeCode' => '',
            'inProgressDemoTestNumber' => '',
            'inProgressNonMotTestNumber' => '',
            'unreadNotificationsCount' => 0,
        ];
        $dashboard = new Dashboard(array_merge($dashboardDataDefaults, $this->dashboardData));

        return new DashboardViewModelBuilder(
            $this->mockMotFrontendIdentityInterface,
            $dashboard,
            $this->mockDashboardGuard,
            $this->mockUrl
        );
    }

    /**
     * @return array
     */
    public function shouldShowContingencyTestsDataProvider()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false],
        ];
    }
}
