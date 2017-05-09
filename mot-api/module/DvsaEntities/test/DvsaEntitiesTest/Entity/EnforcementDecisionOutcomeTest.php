<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementDecisionOutcome;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementDecisionOutcomeTest.
 */
class EnforcementDecisionOutcomeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decisionOutcome = new EnforcementDecisionOutcome();
        $this->assertNull($decisionOutcome->getId());
        $this->assertNull($decisionOutcome->getOutcome());
        $this->assertNull($decisionOutcome->getPosition());
    }

    public function testFluentInterface()
    {
        $decisionOutcome = new EnforcementDecisionOutcome();
        $decisionOutcome->setId(1)
            ->setOutcome('Immediate')
            ->setPosition(2);

        $this->assertEquals(1, $decisionOutcome->getId());
        $this->assertEquals('Immediate', $decisionOutcome->getOutcome());
        $this->assertEquals(2, $decisionOutcome->getPosition());
    }
}
