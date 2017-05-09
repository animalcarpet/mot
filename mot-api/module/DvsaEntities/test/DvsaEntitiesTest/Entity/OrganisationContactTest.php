<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContact;
use DvsaEntities\Entity\OrganisationContactType;
use PHPUnit_Framework_TestCase;

/**
 * Class OrganisationContactTest.
 */
class OrganisationContactTest extends PHPUnit_Framework_TestCase
{
    /** @var Organisation */
    private $org;
    /** @var ContactDetail */
    private $contactDetail;
    /** @var OrganisationContact */
    private $orgContact;

    public function setUp()
    {
        $this->contactDetail = new ContactDetail();
        $this->org = new Organisation();
        $type = new OrganisationContactType();
        $this->org->setContact($this->contactDetail, $type);
        $this->orgContact = new OrganisationContact($this->contactDetail, $type);
    }

    public function testSettersAndGetters()
    {
        $this->orgContact->setOrganisation($this->org);
        $this->assertEquals($this->contactDetail, $this->orgContact->getDetails());
        $this->assertEquals($this->org, $this->orgContact->getOrganisation());
    }
}
