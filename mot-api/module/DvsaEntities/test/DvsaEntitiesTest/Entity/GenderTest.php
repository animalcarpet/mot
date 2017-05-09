<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Gender;
use PHPUnit_Framework_TestCase;

/**
 * Class GenderTest.
 */
class GenderTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $gender = new Gender();
        $gender->getName();
    }
}
