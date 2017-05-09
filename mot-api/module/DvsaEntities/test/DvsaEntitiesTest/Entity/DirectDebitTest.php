<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DirectDebit;
use DvsaEntities\Entity\DirectDebitStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectDebitTest.
 */
class DirectDebitTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $ddStatus = new DirectDebitStatus();
        $org = new Organisation();
        $lastIncrementDate = new \DateTime();
        $nextCollectionDate = new \DateTime();
        $setupDate = new \DateTime();
        $slotsCount = 456;
        $person = new Person();
        $mandateId = '124';
        $dd = new DirectDebit();
        $dd->setStatus($ddStatus)
            ->setOrganisation($org)
            ->setLastIncrementDate($lastIncrementDate)
            ->setSlots($slotsCount)
            ->setNextCollectionDate($nextCollectionDate)
            ->setSetupDate($setupDate)
            ->setPerson($person)
            ->setMandateReference($mandateId);

        $this->assertEquals($ddStatus, $dd->getStatus());
        $this->assertEquals($org, $dd->getOrganisation());
        $this->assertEquals($lastIncrementDate, $dd->getLastIncrementDate());
        $this->assertEquals($slotsCount, $dd->getSlots());
        $this->assertEquals($nextCollectionDate, $dd->getNextCollectionDate());
        $this->assertEquals($setupDate, $dd->getSetupDate());
        $this->assertEquals($person, $dd->getPerson());
        $this->assertEquals($mandateId, $dd->getMandateReference());
    }
}
