<?php

namespace DashboardTest\ViewModel;

use Dashboard\Model\Dashboard;
use Dashboard\Model\PersonalDetails;
use Dashboard\Security\DashboardGuard;
use Dashboard\ViewModel\DashboardViewModelBuilder;
use Dashboard\ViewModel\LinkViewModel;
use DvsaCommon\Auth\PermissionInSystem;
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

    /** @var  array $authenticatedData */
    private $authenticatedData;

    public function setUp()
    {
        $this->dashboardData = [];
        $this->authenticatedData = [];
        $this->authorisationServiceMock = new AuthorisationServiceMock();
    }

    public function testInProgressDemoTestNumberIsPassedToDemoTestViewModel()
    {
        $inProgressDemoTestNumber = '989978';
        $dashboardViewModelBuilder =
            $this->withDashboardData(['inProgressDemoTestNumber' => $inProgressDemoTestNumber])
            ->buildDashboardViewModelBuilder();

        $demoTestViewModel = $dashboardViewModelBuilder->build()->getDemoTestViewModel();

        $this->assertLinkHrefEndsWithDemoTestNumber($inProgressDemoTestNumber, $demoTestViewModel->getLinkViewModel());
    }

    public function testDemoTestIsVisibleToUserWithPerformDemoTestPermission()
    {
        $dashboardViewModelBuilder = $this
            ->withPermissionGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM)
            ->buildDashboardViewModelBuilder();

        $demoTestViewModel = $dashboardViewModelBuilder->build()->getDemoTestViewModel();

        $this->assertTrue($demoTestViewModel->isVisible());
    }

    public function testDemoTestIsNotVisibleToUserWithoutPerformDemoTestPermission()
    {
        $dashboardViewModelBuilder = $this->buildDashboardViewModelBuilder();

        $demoTestViewModel = $dashboardViewModelBuilder->build()->getDemoTestViewModel();

        $this->assertFalse($demoTestViewModel->isVisible());
    }

    /**
     * @param int           $demoTestNumber
     * @param LinkViewModel $linkViewModel
     */
    private function assertLinkHrefEndsWithDemoTestNumber($demoTestNumber, LinkViewModel $linkViewModel)
    {
        $this->assertRegExp("/$demoTestNumber\$/", $linkViewModel->getHref());
    }

    /**
     * @param string $permission
     *
     * @return $this
     */
    private function withPermissionGranted($permission)
    {
        $this->authorisationServiceMock->granted($permission);

        return $this;
    }

    /**
     * @param array $dashboardData
     *
     * @return $this
     */
    private function withDashboardData(array $dashboardData)
    {
        $this->dashboardData = $dashboardData;

        return $this;
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

        $dashboard = new Dashboard(array_merge($dashboardDataDefaults, $this->dashboardData));
        $dashboardGuard = new DashboardGuard($this->authorisationServiceMock);
        $url = XMock::of(Url::class);

        return new DashboardViewModelBuilder($dashboard, $dashboardGuard, $url);
    }
}
