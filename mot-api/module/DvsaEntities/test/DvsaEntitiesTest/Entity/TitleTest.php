<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Title;
use PHPUnit_Framework_TestCase;

/**
 * Class TitleTest.
 */
class TitleTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $title = new Title();
        $title->getName();
        $title->getCode();
    }
}
