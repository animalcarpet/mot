<?php
namespace DvsaCommonTest\Date;

use DvsaCommon\Date\Month;

class MonthTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validationMonthsProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidationInConstructor($year, $month)
    {
        new Month($year, $month);
    }

    public function validationMonthsProvider()
    {
        return [
            ['two thousand', 3], // non numeric year
            ['2013', 'August'], // non numeric month
            ['2013', 13], // too big month
            ['2013', 0], // too small month
        ];
    }

    public function testEquality()
    {
        $monthA = new Month(2012, 3);
        $monthB = new Month('2012', '3');
        $monthC = new Month(2014, 3);
        $monthD = new Month(2012, 5);

        $this->assertTrue($monthA->equals($monthB));
        $this->assertFalse($monthA->equals($monthC));
        $this->assertFalse($monthA->equals($monthD));
    }

    /**
     * @dataProvider previousDatesProvider
     *
     * @param Month $currentMonth
     * @param Month $expectedPreviousMonth
     */
    public function testPrevious_previousMonthIsCalculatedCorrectly(Month $currentMonth, Month $expectedPreviousMonth)
    {
        $actualPreviousMonth = $currentMonth->previous();

        $this->assertTrue($expectedPreviousMonth->equals($actualPreviousMonth));
    }

    public function previousDatesProvider()
    {
        return [
            [new Month(2012, 12), new Month(2012, 11)],
            [new Month(2013, 10), new Month(2013, 9)],
            [new Month(2010, 1), new Month(2009, 12)],
        ];
    }

    /**
     * @dataProvider startDateProvider
     *
     * @param $year
     * @param $month
     * @param $expectedDateString
     */
    public function testGetStartDateAsString_IsCalculatedCorrectly($year, $month, $expectedDateString)
    {
        $month = new Month($year, $month);

        $actualDateString = $month->getStartDateAsString();

        $this->assertEquals($expectedDateString, $actualDateString);
    }

    public function startDateProvider()
    {
        return [
            [2012, 11, '2012-11-01 00:00:00'], // integers are handled
            ['2012', '11', '2012-11-01 00:00:00'], // strings are handled
        ];
    }

    /**
     * @dataProvider endDateProvider
     *
     * @param $year
     * @param $month
     * @param $expectedDateString
     */
    public function testGetEndDateAsString_IsCalculatedCorrectly($year, $month, $expectedDateString)
    {
        $month = new Month($year, $month);

        $actualDateString = $month->getEndDateAsString();

        $this->assertEquals($expectedDateString, $actualDateString);
    }

    public function endDateProvider()
    {
        return [
            [2012, 11, '2012-11-30 23:59:59'], // integers are handled
            ['2012', '2', '2012-02-29 23:59:59'], // strings are handled
        ];
    }

    /**
     * @dataProvider greaterThanProvider
     *
     * @param $monthA
     * @param $monthB
     * @param $expectedResult
     */
    public function testGreaterThan(Month $monthA, Month $monthB, $expectedResult)
    {
        $this->assertEquals($expectedResult, $monthA->greaterThan($monthB));
    }

    public function greaterThanProvider()
    {
        return [
            [new Month(2010, 10), new Month(2010, 10), false],

            [new Month(2010, 11), new Month(2010, 10), true],
            [new Month(2010, 10), new Month(2010, 11), false],

            [new Month(2009, 10), new Month(2008, 12), true],
            [new Month(2009, 10), new Month(2010, 9), false],
        ];
    }
}
