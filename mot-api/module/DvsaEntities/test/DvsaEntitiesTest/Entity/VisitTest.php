<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Visit;
use PHPUnit_Framework_TestCase;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\VisitReason;
use DvsaEntities\Entity\EnforcementVisitOutcome;

/**
 * Class VisitTest.
 */
class VisitTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $visit = new Visit();
        $this->assertNull($visit->getId());
        $this->assertNull($visit->getVehicleTestingStation());
        $this->assertNull($visit->getVisitDate());
        $this->assertNull($visit->getVisitReason());
        $this->assertNull($visit->getVisitOutcome());
    }

    public function testFluentInterface()
    {
        $visit = new Visit();
        $visit->setId(1)
            ->setVehicleTestingStation(new Site())
            ->setVisitDate(new \DateTime('2014-01-01'))
            ->setVisitReason(new VisitReason())
            ->setVisitOutcome(new EnforcementVisitOutcome());

        $this->assertEquals(1, $visit->getId());
        $this->assertEquals('2014-01-01', $visit->getVisitDate()->format('Y-m-d'));
        $this->assertInstanceof(
            \DvsaEntities\Entity\Site::class,
            $visit->getVehicleTestingStation()
        );
        $this->assertInstanceof(
            \DvsaEntities\Entity\VisitReason::class,
            $visit->getVisitReason()
        );
        $this->assertInstanceof(
            \DvsaEntities\Entity\EnforcementVisitOutcome::class,
            $visit->getVisitOutcome()
        );
    }
}
