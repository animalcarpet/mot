<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DirectDebitHistoryStatus;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectDebitHistoryStatusTest.
 */
class DirectDebitHistoryStatusTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $ddHistoryStatus = new DirectDebitHistoryStatus();
        $ddHistoryStatus->setName('name');

        $this->assertEquals('name', $ddHistoryStatus->getName());
    }
}
