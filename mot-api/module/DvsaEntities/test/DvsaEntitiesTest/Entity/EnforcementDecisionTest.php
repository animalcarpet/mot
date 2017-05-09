<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementDecision;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementDecisionTest.
 */
class EnforcementDecisionTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decision = new EnforcementDecision();
        $this->assertNull($decision->getId());
        $this->assertNull($decision->getDecision());
        $this->assertNull($decision->getPosition());
    }

    public function testFluentInterface()
    {
        $decision = new EnforcementDecision();
        $decision->setId(1)
            ->setDecision('Defect missed')
            ->setPosition(9);
        $this->assertEquals(1, $decision->getId());
        $this->assertEquals('Defect missed', $decision->getDecision());
        $this->assertEquals(9, $decision->getPosition());
    }
}
