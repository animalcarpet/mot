<?php

namespace OrganisationApiTest\Mapper;

use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaEntities\Entity\Person;
use OrganisationApi\Service\Mapper\PersonMapper;

/**
 * unit tests for PersonMapper.
 */
class PersonMapperTest extends \PHPUnit_Framework_TestCase
{
    public function test_toArray_anyPerson()
    {
        $mapper = new PersonMapper();

        $result = $mapper->toArray(self::getPersonEntity('personmappertest@'.EmailAddressValidator::TEST_DOMAIN));
        $this->assertTrue(is_array($result));
        $this->assertCount(9, $result);
    }

    public function test_toArray_AepOnly()
    {
        $mapper = new PersonMapper();

        $result = $mapper->toArray(new Person());
        $this->assertTrue(is_array($result));
        $this->assertCount(8, $result);
    }

    public function testToDto()
    {
        $mapper = new PersonMapper();

        $result = $mapper->toDto(self::getPersonEntity('personmappertest@'.EmailAddressValidator::TEST_DOMAIN));
        $this->assertInstanceOf(PersonDto::class, $result);
    }

    public function testManyToArray()
    {
        $mapper = new PersonMapper();

        $result = $mapper->manyToArray([self::getPersonEntity('personmappertest@'.EmailAddressValidator::TEST_DOMAIN)]);
        $this->assertTrue(is_array($result));
        $this->assertCount(1, $result);
    }

    public static function getPersonEntity($username)
    {
        $person = new Person();
        $person
            ->setUsername($username)
            ->setDateOfBirth(new \DateTime());

        return $person;
    }
}
