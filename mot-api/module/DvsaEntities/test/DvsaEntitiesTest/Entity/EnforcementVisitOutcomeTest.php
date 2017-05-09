<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementVisitOutcome;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementVisitOutcomeTest.
 */
class EnforcementVisitOutcomeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decisionOutcome = new EnforcementVisitOutcome();
        $this->assertNull($decisionOutcome->getId());
        $this->assertNull($decisionOutcome->getDescription());
        $this->assertNull($decisionOutcome->getPosition());
    }

    public function testFluentInterface()
    {
        $visitOutcome = new EnforcementVisitOutcome();
        $visitOutcome->setId(1)
            ->setDescription('test')
            ->setPosition(2);

        $this->assertEquals(1, $visitOutcome->getId());
        $this->assertEquals('test', $visitOutcome->getDescription());
        $this->assertEquals(2, $visitOutcome->getPosition());
    }
}
