<?php

namespace OrganisationApiTest\Service\Mapper;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContact;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use OrganisationApi\Service\Mapper\OrganisationMapper;

/**
 * Class OrganisationMapperTest.
 */
class OrganisationMapperTest extends \PHPUnit_Framework_TestCase
{
    /* @var OrganisationMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new OrganisationMapper();
    }

    public function testMapper()
    {
        $result = $this->mapper->manyToDto([$this->getEntity()]);
        /** @var OrganisationDto $result */
        $result = array_pop($result);
        $this->assertInstanceOf(OrganisationDto::class, $result);
        $this->assertEquals('organisationType', $result->getOrganisationType());
        $this->assertEquals('companyType', $result->getCompanyType());
    }

    protected function getEntity()
    {
        $organisationType = (new OrganisationType())
            ->setName('organisationType');
        $companyType = (new CompanyType())
            ->setName('companyType');

        $authForAe = (new AuthorisationForAuthorisedExaminer())
            ->setStatus((new AuthForAeStatus())->setCode('code')->setName('name'))
            ->setNumber('number');
        $address = (new Address())
            ->setAddressLine1('addressLine1')
            ->setAddressLine2('addressLine2')
            ->setAddressLine3('addressLine3')
            ->setTown('town')
            ->setPostcode('postcode');
        $email = (new Email())
            ->setIsPrimary(true)
            ->setEmail('organisationmappertest@'.EmailAddressValidator::TEST_DOMAIN);
        $phone = (new Phone())
            ->setIsPrimary(true)
            ->setNumber('0123456789')
            ->setContactType((new PhoneContactType())->setName(PhoneContactTypeCode::BUSINESS));
        $contact = (new ContactDetail())
            ->setAddress($address)
            ->addEmail($email)
            ->addPhone($phone);

        $organisation = (new Organisation())
            ->setOrganisationType($organisationType)
            ->setCompanyType($companyType)
            ->setId(1)
            ->setRegisteredCompanyNumber('AE123456')
            ->setName('name')
            ->setTradingAs('tradingAs')
            ->setSlotBalance(10)
            ->setDataMayBeDisclosed(1)
            ->setAuthorisedExaminer($authForAe);

        $organisation->addContact(new OrganisationContact($contact, new OrganisationContactType()));

        return $organisation;
    }
}
