<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\PersonContactType;
use PHPUnit_Framework_TestCase;

/**
 * Class PersonContactTypeTest.
 */
class PersonContactTypeTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $personContactType = new PersonContactType();
        $personContactType->setName('name');
        $this->assertEquals('name', $personContactType->getName());
    }
}
