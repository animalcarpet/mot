<?php

namespace SiteTest\Service;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use Site\Service\SiteBreadcrumbsBuilder;
use Zend\View\Helper\Url;

class SiteBreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_VTS_NAME = "testVTS";
    const ORGANISATION_ID = 321;
    const TEST_AE_NAME = "AEname";

    /** @var  AuthorisationServiceMock | \PHPUnit_Framework_MockObject_MockObject */
    private $auth;
    /** @var  SiteBreadcrumbsBuilder | \PHPUnit_Framework_MockObject_MockObject */
    private $siteBreadcrumbsBuilder;
    /** @var  Url | \PHPUnit_Framework_MockObject_MockObject */
    private $url;

    public function setUp()
    {
        $this->url = $url = XMock::of(Url::class);
        $this->auth = new AuthorisationServiceMock();
        $this->siteBreadcrumbsBuilder = new SiteBreadcrumbsBuilder($this->url, $this->auth);
    }

    public function testBreadcrumbsForSiteWithoutAe()
    {
        $this->url->method('__invoke')->willReturn('vtsurl');
        $site = new SiteDto();
        $site->setName(self::TEST_VTS_NAME);
        $breadcrumbs = $this->siteBreadcrumbsBuilder->buildBreadcrumbs($site);
        $this->assertSame([self::TEST_VTS_NAME => 'vtsurl'], $breadcrumbs);
    }

    public function testBreadcrumbsWithoutPremissionToAe()
    {
        $site = new SiteDto();
        $site->setOrganisation((new OrganisationDto())->setName(self::TEST_AE_NAME));
        $site->setName(self::TEST_VTS_NAME);
        $breadcrumbs = $this->siteBreadcrumbsBuilder->buildBreadcrumbs($site);
        $this->assertSame([self::TEST_VTS_NAME => null], $breadcrumbs);
    }

    public function testBreadcrumbsWithPremissionAtAe()
    {
        $this->auth->grantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, self::ORGANISATION_ID);
        $this->checkBreadcrumbsWithAe();
    }

    public function testBreadcrumbsWithPremissionAtSystem()
    {
        $this->auth->granted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL);
        $this->checkBreadcrumbsWithAe();
    }

    public function testBreadcrumbsWithPremissionToAeAndUrl()
    {
        $this->auth->grantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, self::ORGANISATION_ID);
        $this->checkBreadcrumbsWithAe();
    }

    public function checkBreadcrumbsWithAe()
    {
        $site = new SiteDto();
        $site->setOrganisation((new OrganisationDto())->setName(self::TEST_AE_NAME)->setId(self::ORGANISATION_ID));
        $site->setName(self::TEST_VTS_NAME);
        $breadcrumbs = $this->siteBreadcrumbsBuilder->buildBreadcrumbs($site);
        $this->assertSame([
            self::TEST_AE_NAME => null,
            self::TEST_VTS_NAME => null,
        ], $breadcrumbs);
    }
}