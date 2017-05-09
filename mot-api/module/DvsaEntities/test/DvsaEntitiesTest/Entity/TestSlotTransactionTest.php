<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Payment;
use DvsaEntities\Entity\TestSlotTransaction;
use PHPUnit_Framework_TestCase;

/**
 * Class TestSlotTransactionTest.
 */
class TestSlotTransactionTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $start = new \DateTime('now');
        $testSlotTransaction = new TestSlotTransaction();
        $end = new \DateTime('now');

        $this->assertNull($testSlotTransaction->getId());
        $this->assertNull($testSlotTransaction->getSlots());
        $this->assertNull($testSlotTransaction->getStatus());
        $this->assertNull($testSlotTransaction->getPayment());
        $this->assertNull($testSlotTransaction->getOrganisation());

        $this->assertGreaterThanOrEqual($start, $testSlotTransaction->getCreated());
        $this->assertLessThanOrEqual($end, $testSlotTransaction->getCreated());

        $this->assertNull($testSlotTransaction->getCompletedOn());
    }

    public function testSetsPropertiesCorrectly()
    {
        $data = [
            'slots' => 25,
            'status' => 'complete',
            'payment' => new Payment(),
            'organisation' => new Organisation(),
            'created' => new \DateTime('now'),
            'completedOn' => new \DateTime('now'),
            'reference' => uniqid('ref'),
        ];

        $testSlotTransaction = new TestSlotTransaction();
        $testSlotTransaction->setSlots($data['slots'])
            ->setStatus($data['status'])
            ->setPayment($data['payment'])
            ->setOrganisation($data['organisation'])
            ->setCreated($data['created'])
            ->setSalesReference($data['reference'])
            ->setCompletedOn($data['completedOn']);

        $this->assertEquals($data['slots'], $testSlotTransaction->getSlots());
        $this->assertEquals($data['status'], $testSlotTransaction->getStatus());
        $this->assertEquals($data['payment'], $testSlotTransaction->getPayment());
        $this->assertEquals($data['organisation'], $testSlotTransaction->getOrganisation());
        $this->assertEquals($data['created'], $testSlotTransaction->getCreated());
        $this->assertEquals($data['completedOn'], $testSlotTransaction->getCompletedOn());
        $this->assertEquals($data['reference'], $testSlotTransaction->getSalesReference());
        $this->assertNotEmpty($testSlotTransaction->getUniqueIdentifier());
    }
}
