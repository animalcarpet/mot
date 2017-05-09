<?php

namespace DvsaEntitiesTest\Entity;

use DateTime;
use DvsaEntities\Entity\EnforcementMotTestResult;
use DvsaEntities\Entity\EnforcementDecisionOutcome;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementTestResultTest.
 */
class EnforcementMotTestResultTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $testResult = new EnforcementMotTestResult();
        $this->assertNull($testResult->getId());
        $this->assertNull($testResult->getTotalScore());
        $this->assertNull($testResult->getDecisionOutcome());
        $this->assertNull($testResult->getComment());
    }

    public function testFluentInterface()
    {
        $testResult = new EnforcementMotTestResult();
        $comment = new Comment();
        $decisionOutcome = new EnforcementDecisionOutcome();
        $score = 42;
        $id = 1;
        $now = new DateTime();
        $version = 5;
        $user = new Person();

        $testResult->setId($id)
            ->setTotalScore($score)
            ->setComment($comment)
            ->setDecisionOutcome($decisionOutcome);

        $this->assertEquals($id, $testResult->getId());
        $this->assertEquals($score, $testResult->getTotalScore());
        $this->assertInstanceOf(\DvsaEntities\Entity\EnforcementDecisionOutcome::class, $testResult->getDecisionOutcome());
        $this->assertInstanceOf(\DvsaEntities\Entity\Comment::class, $testResult->getComment());
    }
}
