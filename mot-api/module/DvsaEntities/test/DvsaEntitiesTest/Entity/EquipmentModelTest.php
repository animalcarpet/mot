<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EquipmentModel;
use PHPUnit_Framework_TestCase;

/**
 * Class EquipmentModelTest.
 */
class EquipmentModelTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $eqModel = new EquipmentModel();
        $eqModel->getName();
        $eqModel->getType();
        $eqModel->getMake();
    }
}
