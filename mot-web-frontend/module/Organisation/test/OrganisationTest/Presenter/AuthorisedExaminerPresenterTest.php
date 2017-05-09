<?php

namespace OrganisationTest\Presenter;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use Organisation\Presenter\AuthorisedExaminerPresenter;

/**
 * Class PresenterTest.
 */
class AuthorisedExaminerPresenterTest extends \PHPUnit_Framework_TestCase
{
    const AE_ID = 1;
    const AE_NAME = 'name';
    const AE_SLOT = 1234;
    const AE_NUMBER = 'number';
    const AE_TRADING_NAME = 'trading';
    const AE_COMPANY_NUMBER = 'company number';
    const AE_TYPE = CompanyTypeCode::COMPANY;
    const AE_ORG_TYPE = 'org';
    const ADDRESS_LINE_1 = 'line1';
    const TOWN = 'town';
    const POSTCODE = 'postcode';
    const ADDRESS_INLINE = 'line1, town, postcode';

    public function testGetter()
    {
        $presenter = new AuthorisedExaminerPresenter($this->getOrganisationDto());

        $this->assertEquals(self::AE_SLOT, $presenter->getSlotsBalance());
        $this->assertEquals(self::AE_NAME, $presenter->getName());
        $this->assertEquals(self::AE_NUMBER, $presenter->getNumber());
        $this->assertEquals(self::AE_TRADING_NAME, $presenter->getTradingName());
        $this->assertEquals(self::ADDRESS_INLINE, $presenter->getAddressInline());
        $this->assertEquals(self::AE_COMPANY_NUMBER, $presenter->getCompanyNumber());
        $this->assertEquals(self::AE_TYPE, $presenter->getCompanyType());
        $this->assertEquals(self::AE_ORG_TYPE, $presenter->getOrganisationType());
    }

    public function testUrls()
    {
        $presenter = new AuthorisedExaminerPresenter($this->getOrganisationDto());

        $this->assertEquals(AuthorisedExaminerUrlBuilderWeb::principals(self::AE_ID), $presenter->getPrincipalsUrl());
        $this->assertEquals(AuthorisedExaminerUrlBuilderWeb::roles(self::AE_ID), $presenter->getAssignRoleUrl());
    }

    private function getOrganisationDto()
    {
        $authorisation = (new AuthorisedExaminerAuthorisationDto())
            ->setAuthorisedExaminerRef(self::AE_NUMBER);

        $address = (new AddressDto())
            ->setAddressLine1(self::ADDRESS_LINE_1)
            ->setTown(self::TOWN)
            ->setPostcode(self::POSTCODE);
        $contact = (new OrganisationContactDto())
            ->setType(OrganisationContactTypeCode::REGISTERED_COMPANY)
            ->setAddress($address);

        $dto = (new OrganisationDto())
            ->setId(self::AE_ID)
            ->setName(self::AE_NAME)
            ->setSlotBalance(self::AE_SLOT)
            ->setTradingAs(self::AE_TRADING_NAME)
            ->setCompanyType(self::AE_TYPE)
            ->setOrganisationType(self::AE_ORG_TYPE)
            ->setRegisteredCompanyNumber(self::AE_COMPANY_NUMBER)
            ->setContacts([$contact])
            ->setAuthorisedExaminerAuthorisation($authorisation);

        return $dto;
    }
}
