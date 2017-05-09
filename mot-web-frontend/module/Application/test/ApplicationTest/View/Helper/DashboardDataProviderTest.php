<?php

namespace ApplicationTest\View\Helper;

use Application\View\Helper\DashboardDataProvider;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Model\AuthorisedExaminer;
use Dashboard\Model\Dashboard;
use Dashboard\Model\Site;
use DashboardTest\Data\ApiDashboardResourceTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaCommonTest\TestUtils\XMock;

class DashboardDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ApiDashboardResource|\PHPUnit_Framework_MockObject_MockObject */
    private $apiServiceMock;

    /** @var MotIdentityProviderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $identityProviderMock;

    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $authServiceMock;

    /** @var DashboardDataProvider */
    private $helper;

    public function setUp()
    {
        $this->createRequiredMockObjects();
    }

    public function testInvokeToNullWithoutIdentity()
    {
        $holder = $this->helper;
        $this->assertNull($holder());
    }

    public function testInvokeToDashboardWithIdentity()
    {
        // This wll create 2 ae and 6 vts started with id 1 to 6, 3 in each ae.
        $this->mockIdentityForAedm();

        $holder = $this->helper;

        /** @var Dashboard $dashboard */
        $dashboard = $holder();

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertCount(6, $this->fetchDashboardAllSites($dashboard));
    }

    public function testDashboardOnlyReturnAssociatedSites()
    {
        $this->createRequiredMockObjects(false);

        // This wll create 2 ae and 6 vts started with id 7 to 12, 3 in each ae.
        $this->mockIdentityForAedm();

        // Our user is only associated to 3 VTS.
        // vts id 8 & 9 from the first ae (ae_id 3) and
        // vts id 11 from the second ae (ae_id 4)
        $expectedVtsIds = [8, 9, 11];

        foreach ($expectedVtsIds as $site_id) {
            $this->authServiceMock->grantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $site_id);
        }

        $holder = $this->helper;

        /** @var Dashboard $dashboard */
        $dashboard = $holder();

        $this->assertInstanceOf(Dashboard::class, $dashboard);

        $site_ids = $this->fetchDashboardAllSites($dashboard);
        $this->assertCount(count($expectedVtsIds), $site_ids);

        foreach ($expectedVtsIds as $site_id) {
            $this->assertContains($site_id, $site_ids);
        }
    }

    private function createRequiredMockObjects($grantedAll = true)
    {
        $this->identityProviderMock = XMock::of(MotIdentityProviderInterface::class);

        $this->apiServiceMock = XMock::of(ApiDashboardResource::class);

        $this->authServiceMock = $grantedAll ?
            new GrantAllAuthorisationServiceStub() :
            new AuthorisationServiceMock();

        $this->helper = new DashboardDataProvider(
            $this->identityProviderMock,
            $this->apiServiceMock,
            $this->authServiceMock
        );
    }

    private function mockIdentityForAedm()
    {
        $apiDashboardResponseMock = ApiDashboardResourceTest::getTestDataForAedm(2, ['vtsCount' => 3]);

        $this->apiServiceMock->expects($this->any())->method('get')->willReturn($apiDashboardResponseMock);

        $identityMock = XMock::of(MotFrontendIdentityInterface::class);
        $identityMock->expects($this->any())->method('getUserId')->willReturn(5);

        $this->identityProviderMock->expects($this->any())->method('getIdentity')->willReturn($identityMock);
    }

    /**
     * @param Dashboard $dashboard
     *
     * @return array
     */
    private function fetchDashboardAllSites($dashboard)
    {
        $sites = [];

        $authorisedExaminers = $dashboard->getAuthorisedExaminers();
        /** @var AuthorisedExaminer $ae */
        foreach ($authorisedExaminers as $ae) {
            /** @var Site $aeSites */
            foreach ($ae->getSites() as $aeSites) {
                array_push($sites, $aeSites->getId());
            }
        }

        return $sites;
    }
}
