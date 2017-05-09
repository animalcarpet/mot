<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\VisitReason;
use PHPUnit_Framework_TestCase;

/**
 * Class VisitReasonTest.
 */
class VisitReasonTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $reasonForVisit = new VisitReason();
        $this->assertNull($reasonForVisit->getId());
        $this->assertNull($reasonForVisit->getReason());
        $this->assertNull($reasonForVisit->getPosition());
    }

    public function testFluentInterface()
    {
        $reasonForVisit = new VisitReason();
        $reasonForVisit->setId(1)
            ->setReason('Test String')
            ->setPosition(2);

        $this->assertEquals(1, $reasonForVisit->getId());
        $this->assertEquals('Test String', $reasonForVisit->getReason());
        $this->assertEquals(2, $reasonForVisit->getPosition());
    }
}
