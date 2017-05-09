<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\EnforcementDecisionCategory;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementDecisionCategoryTest.
 */
class EnforcementDecisionCategoryTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $decisionCategory = new EnforcementDecisionCategory();
        $this->assertNull($decisionCategory->getId());
        $this->assertNull($decisionCategory->getCategory());
        $this->assertNull($decisionCategory->getPosition());
    }

    public function testFluentInterface()
    {
        $decisionCategory = new EnforcementDecisionCategory();
        $decisionCategory->setId(1)
            ->setCategory('Immediate')
            ->setPosition(2);

        $this->assertEquals(1, $decisionCategory->getId());
        $this->assertEquals('Immediate', $decisionCategory->getCategory());
        $this->assertEquals(2, $decisionCategory->getPosition());
    }
}
