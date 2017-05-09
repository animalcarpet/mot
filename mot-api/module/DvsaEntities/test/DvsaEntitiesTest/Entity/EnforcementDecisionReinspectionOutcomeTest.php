<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementDecisionReinspectionOutcome;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementDecisionReinspectionOutcomeTest.
 */
class EnforcementDecisionReinspectionOutcomeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decisionReinspectionOutcome = new EnforcementDecisionReinspectionOutcome();
        $this->assertNull($decisionReinspectionOutcome->getId());
        $this->assertNull($decisionReinspectionOutcome->getDecision());
        $this->assertNull($decisionReinspectionOutcome->getPosition());
    }

    public function testFluentInterface()
    {
        $decisionReinspectionOutcome = new EnforcementDecisionReinspectionOutcome();
        $decisionReinspectionOutcome->setId(1)
            ->setDecision('Test')
            ->setPosition(2);

        $this->assertEquals(1, $decisionReinspectionOutcome->getId());
        $this->assertEquals('Test', $decisionReinspectionOutcome->getDecision());
        $this->assertEquals(2, $decisionReinspectionOutcome->getPosition());
    }
}
