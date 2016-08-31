<?php
namespace DvsaCommonTest\Date;

use DvsaCommon\Date\TimeSpan;

/**
 * Immutable structure to hold a period of time.
 *
 * Class TimeSpanTest
 * @package DvsaCommonTest\Date
 */
class TimeSpanTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOverflowSeconds()
    {
        $timeSpan = new TimeSpan(2, 5, 4, 66);

        $this->assertEquals(6, $timeSpan->getSeconds());
    }

    public function testGetOverflowMinutes()
    {
        $timeSpan = new TimeSpan(2, 5, 124, 66);

        $this->assertEquals(5, $timeSpan->getMinutes());
    }

    public function testGetOverflowHours()
    {
        $timeSpan = new TimeSpan(2, 27, 58, 166);

        $this->assertEquals(4, $timeSpan->getHours());
    }

    public function testGetOverflowDays()
    {
        $timeSpan = new TimeSpan(2, 23, 59, 60);

        $this->assertEquals(3, $timeSpan->getDays());
    }

    public function testTotalSeconds()
    {
        $timeSpan = new TimeSpan(2, 5, 4, 66);

        $this->assertEquals(191106, $timeSpan->getTotalSeconds());
    }

    public function testTotalMinutes()
    {
        $timeSpan = new TimeSpan(2, 5, 124, 66);

        $this->assertEquals(3305, $timeSpan->getTotalMinutes());
    }

    public function testTotalHours()
    {
        $timeSpan = new TimeSpan(2, 27, 58, 166);

        $this->assertEquals(76, $timeSpan->getTotalHours());
    }

    public function testTotalDays()
    {
        $timeSpan = new TimeSpan(2, 23, 59, 60);

        $this->assertEquals(3, $timeSpan->getTotalDays());
    }

    public function testEquals()
    {
        $timeSpanA = new TimeSpan(2, 23, 59, 60);
        $timeSpanB = new TimeSpan(2, 23, 59, 60);

        $this->assertTrue($timeSpanA->equals($timeSpanB));
    }

    public function testEqualsWithOverflow()
    {
        $timeSpanA = new TimeSpan(2, 23, 59, 60);
        $timeSpanB = new TimeSpan(3, 0, 0, 0);

        $this->assertTrue($timeSpanA->equals($timeSpanB));
    }

    public function testNotEquals()
    {
        $timeSpanA = new TimeSpan(2, 23, 59, 60);
        $timeSpanB = new TimeSpan(2, 23, 59, 61);

        $this->assertFalse($timeSpanA->equals($timeSpanB));
    }

    public function testAddDateTime()
    {
        $dateTime = new \DateTime('2010-12-03 05:07:11');
        $timeSpan = new TimeSpan(1, 2, 3, 4);

        $newDate = $timeSpan->addDateTime($dateTime);

        $this->assertEquals(new \DateTime('2010-12-04 07:10:15'), $newDate);
    }

    public function testAddNegativeDateTime()
    {
        $dateTime = new \DateTime('2010-12-03 05:07:11');
        $timeSpan = new TimeSpan(-1, -2, -3, -4);

        $newDate = $timeSpan->addDateTime($dateTime);

        $this->assertEquals(new \DateTime('2010-12-02 03:04:07'), $newDate);
    }

    public function testSubtractDates()
    {
        $date1 = new \DateTime('2012-05-02 03:04:05');
        $date2 = new \DateTime('2012-05-01 02:03:04');
        $actual = TimeSpan::subtractDates($date1, $date2);
        $this->assertEquals(new TimeSpan(1, 1, 1, 1), $actual);
    }

    public function testSubtractNegativeDates()
    {
        $date1 = new \DateTime('2012-05-01 02:03:04');
        $date2 = new \DateTime('2012-05-02 03:04:05');

        $actual = TimeSpan::subtractDates($date1, $date2);
        $this->assertEquals(new TimeSpan(-1, -1, -1, -1), $actual);
    }

    public function testIsNegative()
    {
        $positive = new TimeSpan(1, -5, -5, -5);
        $negative = new TimeSpan(-1, 5, 5, 5);

        $this->assertTrue($positive->isPositive());
        $this->assertFalse($positive->isNegative());

        $this->assertFalse($negative->isPositive());
        $this->assertTrue($negative->isNegative());
    }

    public function testNegatePositiveDate()
    {
        $timeSpan = new TimeSpan(1, 2, 3, 4);

        $negated = $timeSpan->negate($timeSpan);

        $this->assertEquals(new TimeSpan(-1, -2, -3, -4), $negated);
    }

    public function testNegateNegativeDate()
    {
        $timeSpan = new TimeSpan(-1, -2, -3, -4);

        $negated = $timeSpan->negate($timeSpan);

        $this->assertEquals(new TimeSpan(1, 2, 3, 4), $negated);
    }
}
