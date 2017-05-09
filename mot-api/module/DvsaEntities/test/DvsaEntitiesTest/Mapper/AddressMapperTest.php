<?php

namespace DvsaEntityTest\Mapper;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaEntities\Mapper\AddressMapper;
use DvsaEntities\Entity\Address;

/**
 * Class AddressMapperTest.
 */
class AddressMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var AddressMapper */
    private $addressMapper;

    public function setup()
    {
        $this->addressMapper = new AddressMapper();
    }

    public function testMapToEntitySetsDefaultValues()
    {
        $testData = [];
        $expected = (new Address())
            ->setTown('')
            ->setPostcode('')
            ->setAddressLine1('')
            ->setAddressLine2('')
            ->setAddressLine3('')
            ->setAddressLine4('');

        $actual = $this->addressMapper->mapToEntity(new Address(), $testData);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToEntity()
    {
        $testData = [
            'town' => 'Sm Twn',
            'postcode' => 'S4U 1T1',
            'addressLine1' => '1.',
            'addressLine2' => '2.',
            'addressLine3' => '3.',
            'addressLine4' => '4.',
            'country' => 'Some Country',
        ];
        $expected = (new Address())
            ->setTown('Sm Twn')
            ->setPostcode('S4U 1T1')
            ->setAddressLine1('1.')
            ->setAddressLine2('2.')
            ->setAddressLine3('3.')
            ->setAddressLine4('4.')
            ->setCountry('Some Country');

        $actual = $this->addressMapper->mapToEntity(new Address(), $testData);

        $this->assertEquals($expected, $actual);
    }

    public function testToDto()
    {
        $entity = (new Address())
            ->setTown('Sm Twn')
            ->setPostcode('S4U 1T1')
            ->setAddressLine1('1.')
            ->setAddressLine2('2.')
            ->setAddressLine3('3.')
            ->setAddressLine4('4.');

        $expected = (new AddressDto())
            ->setTown('Sm Twn')
            ->setPostcode('S4U 1T1')
            ->setAddressLine1('1.')
            ->setAddressLine2('2.')
            ->setAddressLine3('3.')
            ->setAddressLine4('4.');

        $actual = $this->addressMapper->toDto($entity);

        $this->assertEquals($expected, $actual);
    }
}
