<?php

namespace OrganisationApiTest\Model\OutputFormat;

use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use OrganisationApi\Model\OutputFormat\OutputFormatOrganisationSlotUsage;

class OutputFormatOrganisationSlotUsageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OutputFormatOrganisationSlotUsage
     */
    private $outputParam;

    public function setUp()
    {
        $this->outputParam = new OutputFormatOrganisationSlotUsage();
    }

    public function testExtractItem()
    {
        $siteId = 1;

        $site = new Site();
        $contactDetail = (new ContactDetail())->setAddress(new Address());

        $site
            ->setId($siteId)
            ->setOrganisation((new Organisation())->setId(999))
            ->setContact($contactDetail, (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS));

        $results = [];
        $item = [
            0 => $site,
            'usage' => 10,
        ];

        $this->outputParam->extractItem($results, 0, $item);

        $this->assertTrue(count($results) > 0);
        $this->assertArrayHasKey($siteId, $results);

        $result = $results[$siteId];

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('usage', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('siteNumber', $result);
        $this->assertArrayHasKey('location', $result);
    }
}
