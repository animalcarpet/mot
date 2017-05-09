<?php

namespace OrganisationTest\UpdateVtsProperty;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\UpdateAeProperty\UpdateAePropertyBreadcrumbs;
use Zend\View\Helper\Url;

class UpdateAePropertyBreadcrumbsTest extends \PHPUnit_Framework_TestCase
{
    const ORG_ID = 2;
    const ORG_NAME = 'orgName';

    const LINK = 'http://link';
    protected $org;

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

        $this->org = new OrganisationDto();
        $this->org->setId(self::ORG_ID);
        $this->org->setName(self::ORG_NAME);
    }

    public function testCreateReturnsArrayOfLinks()
    {
        $propertyName = 'email property';
        $updateVtsPropertyBreadcrumbs = new UpdateAePropertyBreadcrumbs(
            $this->org,
            $this->authorisationService,
            $this->url,
            $propertyName
        );

        $breadcrumbs = $updateVtsPropertyBreadcrumbs->create();

        $expected = [
            self::ORG_NAME => self::LINK,
            $propertyName => '',
        ];

        $this->assertCount(2, $breadcrumbs);
        $this->assertEquals($expected, $breadcrumbs);
    }
}
