<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Date\Time;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use PHPUnit_Framework_TestCase;

/**
 * unit tests for SiteTestingDailySchedule.
 */
class SiteTestingDailyScheduleTest extends PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $siteTestingDailyScheduleTest = new SiteTestingDailySchedule();
        $siteTestingDailyScheduleTest
            ->setOpenTime(Time::fromTimestamp(32400))
            ->setCloseTime(Time::fromTimestamp(45000))
            ->setWeekday(1)
            ->setSite(new Site());

        $this->assertEquals(32400, $siteTestingDailyScheduleTest->getOpenTime()->toTimestamp());
        $this->assertEquals(45000, $siteTestingDailyScheduleTest->getCloseTime()->toTimestamp());
        $this->assertEquals(1, $siteTestingDailyScheduleTest->getWeekday());
        $this->assertInstanceOf(Site::class, $siteTestingDailyScheduleTest->getSite());
    }

    public function dataProvider_isOutsideSchedule()
    {
        $isOpen = false;
        $isClosed = true;

        return [
            [self::generateSchedule($isClosed), '2012-01-01T12:00:00Z',  true],
            [self::generateSchedule($isOpen, '12:00:00', '16:00:00'), '11:59:59',  true],
            [self::generateSchedule($isOpen, '12:00:00', '16:00:00'), '16:00:00',  true],
            [self::generateSchedule($isOpen, '12:00:00', '00:00:00'), '00:00:00',  true],
            [self::generateSchedule($isOpen, '12:00:00', '16:00:00'), '12:00:00',  false],
        ];
    }

    /**
     * @dataProvider dataProvider_isOutsideSchedule
     */
    public function testIsOutsideSchedule($schedule, $referenceTimeString, $result)
    {
        $referenceTime = Time::fromIso8601($referenceTimeString);
        $this->assertEquals($result, SiteTestingDailySchedule::isOutsideSchedule($referenceTime, $schedule));
    }

    public function testGetScheduleForWeekday()
    {
        $expectedSchedule = new SiteTestingDailySchedule();

        $expectedSchedule->setWeekday(1)
            ->setOpenTime(Time::fromIso8601('12:00:00'))
            ->setCloseTime(Time::fromIso8601('16:00:00'))
            ->isClosed(false);

        $this->assertEquals(
            $expectedSchedule,
            SiteTestingDailySchedule::getScheduleForWeekday(self::generateSchedule(false, '12:00:00', '16:00:00'), 1)
        );
    }

    public function testGetScheduleForWeekday_givenInvalidWeekDay_returnsNull()
    {
        $this->assertEquals(
            null,
            SiteTestingDailySchedule::getScheduleForWeekday(self::generateSchedule(false, '12:00:00', '16:00:00'), 8)
        );
    }

    private static function generateSchedule($isClosed = false, $openTime = null, $closeTime = null)
    {
        $arr = [];
        for ($i = 1; $i <= 7; ++$i) {
            $s = new SiteTestingDailySchedule();
            $s->setWeekday($i);
            if (!$isClosed) {
                $s->setOpenTime(Time::fromIso8601($openTime));
                $s->setCloseTime(Time::fromIso8601($closeTime));
            }
            $arr [] = $s;
        }

        return $arr;
    }
}
