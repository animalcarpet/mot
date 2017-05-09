<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\SiteDailyOpeningHours;
use DvsaCommon\Date\Time;

/**
 * Class SiteDailyOpeningHoursTest.
 */
class SiteDailyOpeningHoursTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $hours = (new SiteDailyOpeningHours())
            ->setOpenTime(Time::fromIso8601('12:12:00'))
            ->setCloseTime(Time::fromIso8601('13:13:00'));

        $this->assertEquals('12:12:00', $hours->getOpenTime()->toIso8601());
        $this->assertEquals('13:13:00', $hours->getCloseTime()->toIso8601());
    }

    public function testGetDayName()
    {
        $hours = (new SiteDailyOpeningHours())->setWeekday(2);
        $this->assertEquals('Tuesday', $hours->getDayName());
    }

    public function testIsClosed_returnsTrue()
    {
        $hours = (new SiteDailyOpeningHours())->setOpenTime(null)->setCloseTime(null);
        $this->assertTrue($hours->isClosed());
    }

    public function testIsClosed_returnsFalse()
    {
        $hours = (new SiteDailyOpeningHours())
            ->setOpenTime(Time::fromIso8601('12:13:00'))
            ->setCloseTime(Time::fromIso8601('14:44:00'));
        $this->assertFalse($hours->isClosed());
    }
}
