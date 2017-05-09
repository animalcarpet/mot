<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContact;
use DvsaEntities\Entity\SiteContactType;
use PHPUnit_Framework_TestCase;

/**
 * Class SiteContactTest.
 */
class SiteContactTest extends PHPUnit_Framework_TestCase
{
    const CONTACT_ID = 99999;

    private $site;
    private $contactDetail;
    private $siteContactType;
    /** @var SiteContact */
    private $siteContact;

    public function setUp()
    {
        $this->siteContactType = (new SiteContactType())->setCode(SiteContactTypeCode::CORRESPONDENCE);
        $this->contactDetail = new ContactDetail();
        $this->site = new Site();
        $this->siteContact = new SiteContact($this->contactDetail, $this->siteContactType, $this->site);
        $this->siteContact->setId(self::CONTACT_ID);
    }

    public function testSettersAndGetters()
    {
        $this->assertEquals($this->contactDetail, $this->siteContact->getDetails());
        $this->assertEquals($this->siteContactType, $this->siteContact->getType());
        $this->assertEquals(self::CONTACT_ID, $this->siteContact->getId());
    }
}
