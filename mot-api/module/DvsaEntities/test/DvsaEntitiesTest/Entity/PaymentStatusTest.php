<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\PaymentStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentStatusTest.
 */
class PaymentStatusTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $paymentType = new PaymentStatus();
        $paymentType->setName('name');

        $this->assertEquals('name', $paymentType->getName());
    }
}
