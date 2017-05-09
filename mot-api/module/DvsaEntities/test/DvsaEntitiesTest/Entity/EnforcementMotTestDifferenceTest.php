<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\EnforcementMotTestDifference;
use DvsaEntities\Entity\MotTestType;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementMotTestDifferenceTest.
 */
class EnforcementMotTestDifferenceTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $differences = new EnforcementMotTestDifference();
        $this->assertNull($differences->getMotTestResult());
        $this->assertNull($differences->getRfr());
        $this->assertNull($differences->getMotTest());
        $this->assertNull($differences->getMotTestRfr());
        $this->assertNull($differences->getMotTestType());
        $this->assertEquals(0, $differences->getScore());
        $this->assertNull($differences->getDecision());
        $this->assertNull($differences->getDecisionCategory());
        $this->assertNull($differences->getComment());
        $this->assertEquals(1, $differences->getVersion());
    }

    public function testFluentInterface()
    {
        $differences = new EnforcementMotTestDifference();
        $differences->setId(1)
            ->setMotTestResult(101)
            ->setRfr(102)
            ->setMotTest(103)
            ->setMotTestRfr(104)
            ->setMotTestType((new MotTestType())->setCode(MotTestTypeCode::OTHER))
            ->setScore(106)
            ->setDecision(107)
            ->setDecisionCategory(108)
            ->setComment(109)
            ->setVersion(114);

        $this->assertEquals(101, $differences->getMotTestResult());
        $this->assertEquals(102, $differences->getRfr());
        $this->assertEquals(103, $differences->getMotTest());
        $this->assertEquals(104, $differences->getMotTestRfr());
        $this->assertEquals(
            MotTestTypeCode::OTHER,
            $differences->getMotTestType()->getCode())
        ;
        $this->assertEquals(106, $differences->getScore());
        $this->assertEquals(107, $differences->getDecision());
        $this->assertEquals(108, $differences->getDecisionCategory());
        $this->assertEquals(109, $differences->getComment());
        $this->assertEquals(114, $differences->getVersion());
    }
}
