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

    /** @var DashboardViewModel $dashboardViewModel */
    private $mockDashboardViewModel;

    /** @var DashboardGuard $mockDashboardGuard */
    private $mockDashboardGuard;

    public function setUp()
    {
        $this->dashboardData = [];
        $this->authorisationServiceMock = new AuthorisationServiceMock();
        $this->mockDashboardViewModel = XMock::of(DashboardViewModel::class);
        $this->mockDashboardGuard = XMock::of(DashboardGuard::class);
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
        $this->assertObjectHasAttribute("authorisedExaminersViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("specialNoticesViewModel", $dashboardViewModel);
        $this->assertObjectHasAttribute("authorisedExaminerManagementViewModel", $dashboardViewModel);
    }
    
    public function testInProgressTrainingTestNumberIsPassedToTrainingTestViewModel()
    {
        $inProgressTrainingTestNumber = '989978';

        $this->dashboardData = ['inProgressDemoTestNumber' => $inProgressTrainingTestNumber];

        $trainingTestViewModel = $this->buildDashboardViewModelBuilder()->build()->getTrainingTestViewModel();

        $this->assertLinkHrefEndsWithDemoTestNumber($inProgressTrainingTestNumber, $trainingTestViewModel->getLinkViewModel());
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
                'overdueCount' => 0
            ],
            'overdueSpecialNotices' => [],
            'notifications' => [],
            'inProgressTestNumber' => '',
            'inProgressTestTypeCode' => '',
            'inProgressDemoTestNumber' => '',
            'inProgressNonMotTestNumber' => '',
            'unreadNotificationsCount' => 0,
        ];

        $identityMock = XMock::of(MotFrontendIdentityInterface::class);
        $dashboard = new Dashboard(array_merge($dashboardDataDefaults, $this->dashboardData));
        $urlMock = XMock::of(Url::class);

        return new DashboardViewModelBuilder(
            $identityMock,
            $dashboard,
            $this->mockDashboardGuard,
            $urlMock
        );
    }
}
