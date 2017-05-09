<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EquipmentMake;
use PHPUnit_Framework_TestCase;

/**
 * Class EquipmentMakeTest.
 */
class EquipmentMakeTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $eqMake = new EquipmentMake();
        $eqMake->getName();
    }
}
