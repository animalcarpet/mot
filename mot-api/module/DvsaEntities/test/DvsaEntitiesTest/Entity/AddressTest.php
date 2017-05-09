<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Class AddressTest.
 */
class AddressTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $address = new Address();

        $this->assertNull($address->getId());
        $this->assertNull($address->getAddressLine1());
        $this->assertNull($address->getAddressLine2());
        $this->assertNull($address->getAddressLine3());
        $this->assertNull($address->getAddressLine4());
        $this->assertNull($address->getCountry());
        $this->assertNull($address->getPostcode());
        $this->assertNull($address->getTown());
    }

    public function testSetAddressProperly()
    {
        $person = new Person();

        $data = [
            'id' => '123456789',
            'addressLine1' => '10 Test Road',
            'addressLine2' => 'Second Line',
            'addressLine3' => 'Bristol',
            'addressLine4' => 'South West',
            'country' => 'United Kingdom',
            'createdBy' => $person,
            'createdOn' => new \DateTime(),
            'lastUpdatedBy' => $person,
            'lastUpdatedOn' => new \DateTime(),
            'postcode' => 'BS13',
            'version' => '1',
        ];

        $address = new Address();

        $address->setAddressLine1($data['addressLine1'])
            ->setAddressLine2($data['addressLine2'])
            ->setAddressLine3($data['addressLine3'])
            ->setAddressLine4($data['addressLine4'])
            ->setCountry($data['country'])
            ->setPostcode($data['postcode']);

        $this->assertEquals($data['addressLine1'], $address->getAddressLine1());
        $this->assertEquals($data['addressLine2'], $address->getAddressLine2());
        $this->assertEquals($data['addressLine3'], $address->getAddressLine3());
        $this->assertEquals($data['addressLine4'], $address->getAddressLine4());
        $this->assertEquals($data['country'], $address->getCountry());
        $this->assertEquals($data['postcode'], $address->getPostcode());
    }
}
