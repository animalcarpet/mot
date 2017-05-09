<?php

namespace DvsaClientTest\Entity;

use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;

/**
 * Class AuthorisedExaminerTest.
 */
class AuthorisedExaminerTest extends BaseEntityTestCase
{
    public function testSetProperties()
    {
        $expectedProperties = [
            'id',
            'name',
            'tradingAs',
            'registeredCompanyNumber',
            'organisationType',
            'contacts',
            'slotBalance',
        ];
        $this->checkGettersAndSetters($expectedProperties, new OrganisationDto());
    }

    public function testGetRegisteredCompanyContactDetailWithNoContactsReturnsNull()
    {
        //given
        $ae = $this->createAuthorisedExaminerWithContactDetails([]);

        //when
        $result = $ae->getRegisteredCompanyContactDetail();

        //then
        $this->assertEquals(null, $result);
    }

    public function testGetRegisteredCompanyContactDetailWithNonRegisteredCompanyContactReturnsNull()
    {
        //given
        $cd = new OrganisationContactDto();
        $cd->setType(OrganisationContactTypeCode::CORRESPONDENCE);

        $ae = $this->createAuthorisedExaminerWithContactDetails([$cd]);

        //when
        $result = $ae->getRegisteredCompanyContactDetail();

        //then
        $this->assertEquals(null, $result);
    }

    public function testGetRegisteredCompanyContactDetailWithRegisteredCompanyContactReturnsContactDetail()
    {
        //given
        $cd = new OrganisationContactDto();
        $cd->setType(OrganisationContactTypeCode::REGISTERED_COMPANY);

        $ae = $this->createAuthorisedExaminerWithContactDetails([$cd]);

        //when
        $result = $ae->getRegisteredCompanyContactDetail();

        //then
        $this->assertEquals($cd, $result);
    }

    private function createAuthorisedExaminerWithContactDetails($contactDetailsArray)
    {
        $ae = new OrganisationDto();
        $ae->setContacts($contactDetailsArray);

        return $ae;
    }
}
