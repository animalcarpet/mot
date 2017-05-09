<?php

namespace UserApiTest\HelpDesk\Mapper;

use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Account\AuthenticationMethodDto;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Licence;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\Title;
use UserApi\HelpDesk\Mapper\PersonHelpDeskProfileMapper;

/**
 * Unit tests for PersonHelpDeskProfileMapper.
 */
class PersonHelpDeskProfileMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var PersonHelpDeskProfileMapper $personHelpDeskProfileMapper */
    private $personHelpDeskProfileMapper;

    public function setup()
    {
        $this->personHelpDeskProfileMapper = new PersonHelpDeskProfileMapper();
    }

    public function testFromPersonEntityToDto()
    {
        $person = $this->getPopulatedPersonEntity();
        $expected = $this->getPopulatedDto();

        $actual = $this->personHelpDeskProfileMapper->fromPersonEntityToDto($person);

        $this->assertEquals($expected, $actual);
    }

    public function testFromPersonEntityWithEmptyAddressToDto()
    {
        $person = $this->getPopulatedPersonEntityWithEmptyAddress();
        $expected = $this->getPopulatedDtoWithEmptyAddress();

        $actual = $this->personHelpDeskProfileMapper->fromPersonEntityToDto($person);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return PersonHelpDeskProfileDto
     */
    private function getPopulatedDto()
    {
        return (new PersonHelpDeskProfileDto())
            ->setTitle('Miss')
            ->setFirstName('Test1')
            ->setLastName('Test2')
            ->setDateOfBirth('1992-04-01')
            ->setAddress(
                (new AddressDto())
                    ->setTown('Sm Twn')
                    ->setPostcode('S4U 1T1')
                    ->setAddressLine1('1.')
                    ->setAddressLine2('2.')
                    ->setAddressLine3('3.')
                    ->setAddressLine4('4.')
            )
            ->setTelephone('+768-45-4433630')
            ->setDrivingLicenceNumber('1234567890');
    }

    /**
     * @return PersonHelpDeskProfileDto
     */
    private function getPopulatedDtoWithEmptyAddress()
    {
        return (new PersonHelpDeskProfileDto())
            ->setTitle('Miss')
            ->setFirstName('Test1')
            ->setLastName('Test2')
            ->setDateOfBirth('1992-04-01')
            ->setAddress(null)
            ->setTelephone('+768-45-4433630')
            ->setDrivingLicenceNumber('1234567890');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTitle()
    {
        $title = \DvsaCommonTest\TestUtils\XMock::of(Title::class, ['getName', 'getId']);
        $title->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Miss'));
        $title->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(2));

        return $title;
    }

    /**
     * @return Person
     */
    private function getPopulatedPersonEntity()
    {
        $person = (new Person())
            ->setTitle($this->getMockTitle())
            ->setFirstName('Test1')
            ->setFamilyName('Test2')
            ->setDateOfBirth(DateUtils::toDate('1992-04-01'));

        $licence = (new Licence())->setLicenceNumber('1234567890');
        $person->setDrivingLicence($licence);

        $address = (new Address())
            ->setTown('Sm Twn')
            ->setPostcode('S4U 1T1')
            ->setAddressLine1('1.')
            ->setAddressLine2('2.')
            ->setAddressLine3('3.')
            ->setAddressLine4('4.');
        $phone = (new Phone())
            ->setIsPrimary(true)
            ->setNumber('+768-45-4433630');
        $contactDetail = (new ContactDetail())->setAddress($address)->addPhone($phone);
        $personContactType = new \DvsaEntities\Entity\PersonContactType();
        $personContactType->setName(PersonContactType::PERSONAL);
        $personalContact = new PersonContact($contactDetail, $personContactType, $person);

        $person->addContact($personalContact);

        return $person;
    }

    /**
     * @return Person
     */
    private function getPopulatedPersonEntityWithEmptyAddress()
    {
        $person = (new Person())
            ->setTitle($this->getMockTitle())
            ->setFirstName('Test1')
            ->setFamilyName('Test2')
            ->setDateOfBirth(DateUtils::toDate('1992-04-01'));

        $licence = (new Licence())->setLicenceNumber('1234567890');
        $person->setDrivingLicence($licence);

        $phone = (new Phone())
            ->setIsPrimary(true)
            ->setNumber('+768-45-4433630');
        $contactDetail = (new ContactDetail())->setAddress(null)->addPhone($phone);
        $personContactType = new \DvsaEntities\Entity\PersonContactType();
        $personContactType->setName(PersonContactType::PERSONAL);
        $personalContact = new PersonContact($contactDetail, $personContactType, $person);

        $person->addContact($personalContact);

        return $person;
    }

    public function testFromPersonEntityToDtoWithPinAuthentication()
    {
        $person = $this->getPopulatedPersonEntity();

        $authenticationMethod = new AuthenticationMethod();

        $authenticationMethod
                ->setName('Pin')
                ->setCode('PIN');

        $expected = $this->getPopulatedDto();

        $expected->setAuthenticationMethod(
            (new AuthenticationMethodDto())
                ->setName('Pin')
                ->setCode('PIN')
        );

        $actual = $this->personHelpDeskProfileMapper->fromPersonEntityToDto($person);

        $this->personHelpDeskProfileMapper->mapAuthenticationMethod($authenticationMethod, $actual);

        $this->assertEquals($expected, $actual);
    }
}
