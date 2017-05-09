<?php

namespace OrganisationApiTest\Mapper;

use DvsaCommon\Dto\Organisation\AuthorisedExaminerListItemDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use OrganisationApi\Service\Mapper\AuthorisedExaminerListItemMapper;

/**
 * Test functionality of AuthorisedExaminerListItemMapper class.
 */
class AuthorisedExaminerListItemMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorisedExaminerListItemMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new AuthorisedExaminerListItemMapper();

        parent::setUp();
    }

    public function testToArray()
    {
        $result = $this->mapper->toArray(self::getAuthorisedExaminerEntity());

        $this->assertTrue(is_array($result));
        $this->assertCount(12, $result);
    }

    public function testToDto()
    {
        $result = $this->mapper->toDto(self::getAuthorisedExaminerEntity());

        $this->assertInstanceOf(AuthorisedExaminerListItemDto::class, $result);
    }

    public function testManyToDto()
    {
        $result = $this->mapper->manyToDto([self::getAuthorisedExaminerEntity()]);

        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
        $this->assertInstanceOf(AuthorisedExaminerListItemDto::class, $result[0]);
    }

    public static function getAuthorisedExaminerEntity()
    {
        $address = new Address();
        $address
            ->setAddressLine1('11 St Thomas Street')
            ->setAddressLine2('flat 5')
            ->setTown('Bristol')
            ->setPostcode('BS16JZ');

        $phone = new Phone();
        $phone
            ->setIsPrimary(true)
            ->setNumber('0123456789')
            ->setContactType(
                (new PhoneContactType())
                    ->setName(PhoneContactTypeCode::PERSONAL)
            );

        $contact = new ContactDetail();
        $contact
            ->setAddress($address)
            ->addPhone($phone);

        $orgType = new OrganisationType();
        $orgType->setName('Limited Company');

        $type = new OrganisationContactType();

        $organisation = new Organisation();
        $organisation
            ->setId(1)
            ->setOrganisationType($orgType)
            ->setName('Limited Ae')
            ->setTradingAs('Limited Ae Company')
            ->setSlotBalance(100)
            ->setSlotsWarning(100)
            ->setContact($contact, $type);

        $status = new AuthForAeStatus();
        $status
            ->setId(1)
            ->setName('IN PROGRESS');

        $ae = new AuthorisationForAuthorisedExaminer();
        $ae
            ->setId(1001)
            ->setNumber('AE-5555')
            ->setOrganisation($organisation)
            ->setStatus($status)
            ->setStatusChangedOn(new \DateTime('2011-12-13'))
            ->setValidFrom(new \DateTime('2011-12-13'))
            ->setExpiryDate(new \DateTime('2014-01-02'));

        return $ae;
    }
}
