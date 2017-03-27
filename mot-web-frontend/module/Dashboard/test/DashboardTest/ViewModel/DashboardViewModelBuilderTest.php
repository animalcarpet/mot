<?php

namespace DashboardTest\ViewModel;

use Dashboard\Model\AuthorisedExaminer;
use Dashboard\Model\Dashboard;
use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\DashboardViewModel;
use Dashboard\ViewModel\DashboardViewModelBuilder;
use Dashboard\ViewModel\LinkViewModel;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Enum\SiteBusinessRoleCode;
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

    /** @var bool $isTesterAtAnySite */
    private $isTesterAtAnySite = false;

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
        $this->mockDashboardGuard
            ->method('isDemoTestNeeded')
            ->willReturn(true);

        $this->mockDashboardGuard
            ->method('canViewYourPerformance')
            ->willReturn(true);

        $this->mockDashboardGuard
            ->method('isQualifiedTester')
            ->willReturn(true);

        $this->mockDashboardGuard
            ->method('canGenerateFinancialReports')
            ->willReturn(true);

        $this->mockDashboardGuard
            ->method('isTestingEnabled')
            ->willReturn(true);

        $this->isTesterAtAnySite = true;

        $dashboardViewModelBuilder = $this->buildDashboardViewModelBuilder();

        $dashboardViewModel = $dashboardViewModelBuilder->build();

        $this->assertTrue($dashboardViewModel->getShowDemoMessage());
        $this->assertTrue($dashboardViewModel->getShowYourPerformance());
        $this->assertTrue($dashboardViewModel->getShowContingencyTests());
        $this->assertTrue($dashboardViewModel->getShowFinancialReports());
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

    private function buildAuthorisedExaminersForIsTesterAtAnySite()
    {
        $this->mockDashboardGuard
            ->method('canViewVehicleTestingStation')
            ->willReturn(true);

        $authorisedExaminerData = [
            'id' => 1,
            'reference' => 'AE1234',
            'name' => 'AE1',
            'tradingAs' => 'AE',
            'managerId' => 5,
            'slots' => 200,
            'slotsWarnings' => 50,
            'sites' => [
                0 => [
                    'id' => 1,
                    'name' => 'V1234',
                    'siteNumber' => 'V1',
                    'positions' => [SiteBusinessRoleCode::TESTER]
                ]
            ],
            'position' => ''
        ];

        return [new AuthorisedExaminer($authorisedExaminerData)];
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

        if ($this->isTesterAtAnySite) {
            $dashboard->setAuthorisedExaminers($this->buildAuthorisedExaminersForIsTesterAtAnySite());
        }

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
