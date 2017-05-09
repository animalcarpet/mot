<?php

namespace SiteTest\UpdateVtsProperty;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\UpdateAeProperty\AeBreadcrumbs;
use Zend\View\Helper\Url;

class AeBreadcrumbsTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 2;
    const ORG_NAME = 'orgName';

    const LINK = 'http://link';

    private $url;

    protected function setUp()
    {
        $url = XMock::of(Url::class);
        $url
            ->expects($this->any())
            ->method('__invoke')
            ->willReturn(self::LINK);

        $this->url = $url;
    }

    private function createAuthorisationService($value)
    {
        $authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $authorisationService
            ->expects($this->any())
            ->method('isGrantedAtOrganisation')
            ->willReturn($value);

        return $authorisationService;
    }

    public function testCreateReturnsLinksToSiteAndOrganisation()
    {
        $org = new OrganisationDto();
        $org->setId(self::ORG_ID);
        $org->setName(self::ORG_NAME);

        $aeBreadcrumbs = new AeBreadcrumbs($org, $this->createAuthorisationService(true), $this->url);
        $breadcrumbs = $aeBreadcrumbs->create();

        $expected = [
            self::ORG_NAME => self::LINK,
        ];

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }

    public function testCreateReturnsLinkToSiteWhenUserHasNoPermissionToSeeOrganisationPage()
    {
        $org = new OrganisationDto();
        $org->setId(self::ORG_ID);
        $org->setName(self::ORG_NAME);

        $aeBreadcrumbs = new AeBreadcrumbs($org, $this->createAuthorisationService(false), $this->url);
        $breadcrumbs = $aeBreadcrumbs->create();

        $expected = [];

        $this->assertCount(0, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }
}
