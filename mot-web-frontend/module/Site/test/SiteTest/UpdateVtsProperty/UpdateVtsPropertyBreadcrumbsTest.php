<?php

namespace SiteTest\UpdateVtsProperty;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use Site\UpdateVtsProperty\UpdateVtsPropertyBreadcrumbs;
use Zend\View\Helper\Url;

class UpdateVtsPropertyBreadcrumbsTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 2;
    const ORG_NAME = 'orgName';

    const SITE_ID = 1;
    const SITE_NAME = 'siteName';

    const LINK = 'http://link';

    private $url;
    private $authorisationService;
    private $vtsDto;

    protected function setUp()
    {
        $url = XMock::of(Url::class);
        $url
            ->expects($this->any())
            ->method('__invoke')
            ->willReturn(self::LINK);

        $this->url = $url;

        $authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $authorisationService
            ->expects($this->any())
            ->method('isGrantedAtOrganisation')
            ->willReturn(true);

        $this->authorisationService = $authorisationService;

        $org = new OrganisationDto();
        $org->setId(self::ORG_ID);
        $org->setName(self::ORG_NAME);

        $vtsDto = new VehicleTestingStationDto();
        $vtsDto->setId(self::SITE_ID);
        $vtsDto->setName(self::SITE_NAME);
        $vtsDto->setOrganisation($org);

        $this->vtsDto = $vtsDto;
    }

    public function testCreateReturnsArrayOfLinks()
    {
        $propertyName = 'email property';
        $updateVtsPropertyBreadcrumbs = new UpdateVtsPropertyBreadcrumbs(
            $this->vtsDto,
            $this->authorisationService,
            $this->url,
            $propertyName
        );

        $breadcrumbs = $updateVtsPropertyBreadcrumbs->create();

        $expected = [
            self::ORG_NAME => self::LINK,
            self::SITE_NAME => self::LINK,
            $propertyName => '',
        ];

        $this->assertCount(3, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }
}
