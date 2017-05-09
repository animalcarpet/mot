<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\EnforcementMotDemoTest;
use PHPUnit_Framework_TestCase;

/**
 * Class EnforcementMotDemoTestTest.
 */
class EnforcementMotDemoTestTest extends PHPUnit_Framework_TestCase
{
    public function testFluentInterface()
    {
        $motDemoTest = new EnforcementMotDemoTest();
        $comment = new Comment();

        $motDemoTest
            ->setMotTestId(1)
            ->setIsSatisfactory(1)
            ->setComment($comment)
            ->setId(1);

        $this->assertEquals(1, $motDemoTest->getMotTestId());
        $this->assertEquals(1, $motDemoTest->getIsSatisfactory());
        $this->assertEquals($comment, $motDemoTest->getComment());
        $this->assertEquals(1, $motDemoTest->getId());
    }
}
