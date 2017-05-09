<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DirectDebit;
use DvsaEntities\Entity\DirectDebitHistory;
use DvsaEntities\Entity\DirectDebitHistoryStatus;
use DvsaEntities\Entity\TestSlotTransaction;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectDebitHistoryTest.
 */
class DirectDebitHistoryTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $tx = new TestSlotTransaction();
        $dd = new DirectDebit();
        $ddHistoryStatus = new DirectDebitHistoryStatus();
        $incrementDate = new \DateTime();
        $ddHistory = new DirectDebitHistory();
        $ddHistory->setTransaction($tx)
            ->setDirectDebit($dd)
            ->setIncrementDate($incrementDate)
            ->setStatus($ddHistoryStatus);

        $this->assertEquals($tx, $ddHistory->getTransaction());
        $this->assertEquals($dd, $ddHistory->getDirectDebit());
        $this->assertEquals($incrementDate, $ddHistory->getIncrementDate());
        $this->assertEquals($ddHistoryStatus, $ddHistory->getStatus());
    }
}
