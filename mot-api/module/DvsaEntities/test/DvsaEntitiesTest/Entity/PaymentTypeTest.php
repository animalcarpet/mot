<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\PaymentType;
use PHPUnit_Framework_TestCase;

/**
 * Class PaymentTypeTest.
 */
class PaymentTypeTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $paymentType = new PaymentType();
        $paymentType->setName('name')
            ->setActive(true)
            ->setDisplayOrder(4);
        $paymentType->setIsAdjustable(false);

        $this->assertEquals('name', $paymentType->getName());
        $this->assertTrue($paymentType->getActive());
        $this->assertEquals(4, $paymentType->getDisplayOrder());
        $this->assertFalse($paymentType->getIsAdjustable());
    }
}
