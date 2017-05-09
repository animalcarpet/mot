<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementDecisionScore;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementDecisionScoreTest.
 */
class EnforcementScoreTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decisionScore = new EnforcementDecisionScore();
        $this->assertNull($decisionScore->getId());
        $this->assertNull($decisionScore->getScore());
        $this->assertNull($decisionScore->getDescription());
        $this->assertNull($decisionScore->getPosition());
    }

    public function testFluentInterface()
    {
        $decisionScore = new EnforcementDecisionScore();
        $description = 'This is a description';
        $decisionScore->setId(1)
            ->setScore(10000)
            ->setDescription($description)
            ->setPosition(2);

        $this->assertEquals(1, $decisionScore->getId());
        $this->assertEquals(10000, $decisionScore->getScore());
        $this->assertEquals($description, $decisionScore->getDescription());
        $this->assertEquals(2, $decisionScore->getPosition());
    }
}
