<?php

namespace PersonApiTest\Dto;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\Title;
use MailerApi\Service\MailerService;
use PersonApi\Dto\PersonDetails;

class PersonDetailsTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const FIRST_NAME = 'John';
    const MIDDLE_NAME = 'Steven';
    const SURNAME = 'Smith';
    const DOB = '1980-10-10';
    const TITLE = 'Mr';
    const GENDER = 'Male';
    const ADDRESS_LINE_1 = 'London';
    const ADDRESS_LINE_2 = 'Abc';
    const ADDRESS_LINE_3 = '213';
    const TOWN = 'Dublin';
    const POSTCODE = 'LON 123';
    const EMAIL = MailerService::AWS_MAIL_SIMULATOR_SUCCESS;
    const PHONE = '123456765432';
    const DRIVING_LICENCE_NUMBER = '2343213';
    const REGION = 'Other';
    const ROLE_TESTER = 'tester';
    const POSITIONS = 'test';
    const USERNAME = 'tester1';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var array
     */
    private $roles;

    public function setUp()
    {
        $this->entityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->person = $this->createPerson();
        $this->roles = [];
    }

    /**
     * @group VM-10289
     */
    public function testPersonFieldsAreCorrectlyPopulatedWithEmptyContactDetail()
    {
        $contactDetail = $this->createEmptyContactDetail();
        $this->configureEntityManagerWithNullEntities();

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertEquals(self::ID, $personDetails->getId());
        $this->assertEquals(self::USERNAME, $personDetails->getUsername());
        $this->assertEquals(self::FIRST_NAME, $personDetails->getFirstName());
        $this->assertEquals(self::MIDDLE_NAME, $personDetails->getMiddleName());
        $this->assertEquals(self::SURNAME, $personDetails->getSurname());
        $this->assertEquals(self::TITLE, $personDetails->getTitle());
        $this->assertEquals(self::GENDER, $personDetails->getGender());
    }

    /**
     * @group VM-10289
     */
    public function testImportAddressWithEmptyDataPopulatesWithNullValues()
    {
        $contactDetail = $this->createEmptyContactDetail();
        $this->configureEntityManagerWithNullEntities();

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertNull($personDetails->getAddressLine1());
        $this->assertNull($personDetails->getAddressLine2());
        $this->assertNull($personDetails->getAddressLine3());
        $this->assertNull($personDetails->getTown());
        $this->assertNull($personDetails->getPostcode());
    }

    /**
     * @group VM-10289
     */
    public function testImportAddressPopulatesAddressFields()
    {
        $contactDetail = $this->createValidContactDetail();
        $this->configureEntityManagerWithValidEntities($this->person, $contactDetail);

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertEquals(self::ADDRESS_LINE_1, $personDetails->getAddressLine1());
        $this->assertEquals(self::ADDRESS_LINE_2, $personDetails->getAddressLine2());
        $this->assertEquals(self::ADDRESS_LINE_3, $personDetails->getAddressLine3());
        $this->assertEquals(self::TOWN, $personDetails->getTown());
        $this->assertEquals(self::POSTCODE, $personDetails->getPostcode());
    }

    /**
     * @group VM-10289
     */
    public function testImportPhoneWithEmptyDataPopulatesWithNullValues()
    {
        $contactDetail = $this->createEmptyContactDetail();
        $this->configureEntityManagerWithNullEntities();

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertNull($personDetails->getPhone());
    }

    /**
     * @group VM-10289
     */
    public function testImportPhonePopulatesPhoneField()
    {
        $contactDetail = $this->createValidContactDetail();
        $this->configureEntityManagerWithValidEntities($this->person, $contactDetail);

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertEquals(self::PHONE, $personDetails->getPhone());
    }

    /**
     * @group VM-10289
     */
    public function testImportEmailWithEmptyDataPopulatesWithNullValues()
    {
        $contactDetail = $this->createEmptyContactDetail();
        $this->configureEntityManagerWithNullEntities();

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertNull($personDetails->getEmail());
    }

    /**
     * @group VM-10289
     */
    public function testImportEmailPopulatesEmailField()
    {
        $contactDetail = $this->createValidContactDetail();
        $this->configureEntityManagerWithValidEntities($this->person, $contactDetail);

        $personDetails = new PersonDetails($this->person, $contactDetail, $this->entityManager, $this->roles);

        $this->assertEquals(self::EMAIL, $personDetails->getEmail());
    }

    public function testPersonTitleIsEmptyWhenIdZero()
    {
        $personZero = $this->createPerson(0);
        $contactDetail = $this->createValidContactDetail();

        $this->configureEntityManagerWithValidEntities($personZero, $contactDetail);

        $personDetails = new PersonDetails($personZero, $contactDetail, $this->entityManager, $this->roles);

        $this->assertEmpty($personDetails->getTitle());
    }

    /**
     * @param int $id
     *
     * @return \DvsaEntities\Entity\Person
     */
    private function createPerson($id = 1)
    {
        return (new Person())
            ->setId(self::ID)
            ->setUsername(self::USERNAME)
            ->setFirstName(self::FIRST_NAME)
            ->setMiddleName(self::MIDDLE_NAME)
            ->setFamilyName(self::SURNAME)
            ->setTitle((new Title())->setName(self::TITLE)->setId($id))
            ->setGender((new Gender())->setName(self::GENDER));
    }

    private function configureEntityManagerWithNullEntities()
    {
        $this->configureEntityManager(null, null, null, null, null);
    }

    /**
     * @param Person $person
     */
    private function configureEntityManagerWithValidEntities(Person $person, ContactDetail $contactDetail)
    {
        $phone = (new Phone())->setNumber(self::PHONE);
        $phoneContactType = PhoneContactTypeCode::PERSONAL;
        $email = (new Email())->setEmail(self::EMAIL);
        $personContactType = new \DvsaEntities\Entity\PersonContactType();
        $personContact = new PersonContact($contactDetail, $personContactType, $person);

        $this->configureEntityManager($personContactType, $personContact, $phoneContactType, $phone, $email);
    }

    /**
     * @param $personContactType
     * @param $personContact
     * @param $phoneContactType
     * @param $phone
     * @param $email
     */
    private function configureEntityManager($personContactType, $personContact, $phoneContactType, $phone, $email)
    {
        $personContactRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $personContactRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($personContact));

        $personContactTypeRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $personContactTypeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($personContactType));

        $phoneContactTypeRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phoneContactTypeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($phoneContactType));

        $phoneRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phoneRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($phone));

        $emailRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($email));

        $this
            ->entityManager
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->will($this->returnValueMap([
                [PersonContact::class, $personContactRepository],
                [PhoneContactType::class, $phoneContactTypeRepository],
                [Phone::class, $phoneRepository],
                [Email::class, $emailRepository],
            ]));
    }

    /**
     * @return ContactDetail
     */
    private function createEmptyContactDetail()
    {
        return new ContactDetail();
    }

    /**
     * @return ContactDetail
     */
    private function createValidContactDetail()
    {
        $address = (new Address())
            ->setAddressLine1(self::ADDRESS_LINE_1)
            ->setAddressLine2(self::ADDRESS_LINE_2)
            ->setAddressLine3(self::ADDRESS_LINE_3)
            ->setTown(self::TOWN)
            ->setPostcode(self::POSTCODE);

        $contactDetail = $this
            ->getMockBuilder(ContactDetail::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAddress'])
            ->getMock();
        $contactDetail
            ->expects($this->any())
            ->method('getAddress')
            ->willReturn($address);

        return $contactDetail;
    }
}
