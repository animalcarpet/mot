<?php

namespace DvsaEntityTest\EntityTrait;

use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Test for CommonIdentityTrait.
 */
class CommonIdentityTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testTrait()
    {
        /** @var CommonIdentityTrait $mock */
        $mock = $this->getMockForTrait(\DvsaEntities\EntityTrait\CommonIdentityTrait::class);
        $mock->setId(9999);
        $this->assertEquals(9999, $mock->getId());
    }
}
