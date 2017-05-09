<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DirectDebitStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectDebitStatusTest.
 */
class DirectDebitStatusTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $ddStatus = new DirectDebitStatus();
        $ddStatus->setName('name');
        $ddStatus->setCpmsCode('code');

        $this->assertEquals('name', $ddStatus->getName());
        $this->assertEquals('code', $ddStatus->getCpmsCode());
    }
}
