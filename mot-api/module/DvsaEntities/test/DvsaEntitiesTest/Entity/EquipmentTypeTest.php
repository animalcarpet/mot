<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EquipmentType;
use PHPUnit_Framework_TestCase;

/**
 * Class EquipmentTypeTest.
 */
class EquipmentTypeTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $eqType = new EquipmentType();
        $eqType->getName();
        $eqType->getCode();
    }
}
