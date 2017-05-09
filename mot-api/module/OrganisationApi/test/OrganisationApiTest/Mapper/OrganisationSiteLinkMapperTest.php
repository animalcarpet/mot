<?php

namespace OrganisationApiTest\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationSiteLinkDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\OrganisationSiteStatus;
use DvsaEntities\Entity\Site;
use OrganisationApi\Service\Mapper\OrganisationSiteLinkMapper;

/**
 * Test functionality of OrganisationSiteLinkMapper class.
 */
class OrganisationSiteLinkMapperTest extends \PHPUnit_Framework_TestCase
{
    const LINK_ID = 7777;
    const ORG_ID = 9999;
    const SITE_ID = 8888;

    /** @var OrganisationSiteLinkMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new OrganisationSiteLinkMapper();

        parent::setUp();
    }

    public function testToDto()
    {
        $actial = $this->mapper->toDto(self::getOrganisationSiteMap());

        $this->assertInstanceOf(OrganisationSiteLinkDto::class, $actial);
        $this->assertEquals(self::LINK_ID, $actial->getId());

        $actualOrgDto = $actial->getOrganisation();
        $this->assertInstanceOf(OrganisationDto::class, $actualOrgDto);
        $this->assertEquals(self::ORG_ID, $actualOrgDto->getId());

        $actualSiteDto = $actial->getSite();
        $this->assertInstanceOf(SiteDto::class, $actualSiteDto);
        $this->assertEquals(self::SITE_ID, $actualSiteDto->getId());
    }

    public function testToDtoEntityIsNull()
    {
        $this->assertNull($this->mapper->toDto(null));
    }

    public static function getOrganisationSiteMap()
    {
        $entity = new OrganisationSiteMap();

        $entity
            ->setId(self::LINK_ID)
            ->setStatus(new OrganisationSiteStatus())
            ->setOrganisation((new Organisation())->setId(self::ORG_ID))
            ->setSite((new Site())->setId(self::SITE_ID));

        return $entity;
    }
}
