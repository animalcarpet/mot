<?php

namespace SiteTest\UpdateVtsProperty;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use Site\UpdateVtsProperty\VtsBreadcrumbs;
use Zend\View\Helper\Url;

class VtsBreadcrumbsTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 2;
    const ORG_NAME = 'orgName';

    const SITE_ID = 1;
    const SITE_NAME = 'siteName';

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

        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setId(self::SITE_ID);
        $vtsDto->setName(self::SITE_NAME);
        $vtsDto->setOrganisation($org);

        $vtsBreadcrumbs = new VtsBreadcrumbs($vtsDto, $this->createAuthorisationService(true), $this->url);
        $breadcrumbs = $vtsBreadcrumbs->create();

        $expected = [
            self::ORG_NAME => self::LINK,
            self::SITE_NAME => self::LINK,
        ];

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }

    public function testCreateReturnsLinkToSiteWhenSiteHasNoOrganisationAssociation()
    {
        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setId(self::SITE_ID);
        $vtsDto->setName(self::SITE_NAME);

        $vtsBreadcrumbs = new VtsBreadcrumbs($vtsDto, $this->createAuthorisationService(true), $this->url);
        $breadcrumbs = $vtsBreadcrumbs->create();

        $expected = [
            self::SITE_NAME => self::LINK,
        ];

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }

    public function testCreateReturnsLinkToSiteWhenUserHasNoPermissionToSeeOrganisationPage()
    {
        $org = new OrganisationDto();
        $org->setId(self::ORG_ID);
        $org->setName(self::ORG_NAME);

        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setId(self::SITE_ID);
        $vtsDto->setName(self::SITE_NAME);
        $vtsDto->setOrganisation($org);

        $vtsBreadcrumbs = new VtsBreadcrumbs($vtsDto, $this->createAuthorisationService(false), $this->url);
        $breadcrumbs = $vtsBreadcrumbs->create();

        $expected = [
            self::SITE_NAME => self::LINK,
        ];

        $this->assertCount(1, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }
}
