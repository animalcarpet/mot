<?php
namespace DvsaCommonTest\Date;

use DateTime;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use PHPUnit_Framework_TestCase;

/**
 * Tests for DateUtils class
 */
class DateUtilsTest extends PHPUnit_Framework_TestCase
{
    const FORMAT_DATE_INTERVAL_COMPARISON = '%yy %mm %dd %hh %im %ss';

    public function testIsDateBetween()
    {
        $dt = new \DateTime("1984-12-12 24:00:00");
        $this->assertTrue(
            DateUtils::isDateBetween(
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('2010-10-10')
            )
        );
        $this->assertTrue(
            DateUtils::isDateBetween(
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('1980-10-10'),
                DateUtils::toDate('2010-10-10')
            )
        );
        $this->assertTrue(
            DateUtils::isDateBetween(
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('2030-10-10')
            )
        );

        $this->assertFalse(
            DateUtils::isDateBetween(
                DateUtils::toDate('2010-10-10'),
                DateUtils::toDate('2011-10-10'),
                DateUtils::toDate('2030-10-10')
            )
        );
        $this->assertFalse(
            DateUtils::isDateBetween(
                DateUtils::toDate('2050-10-10'),
                DateUtils::toDate('2011-10-10'),
                DateUtils::toDate('2020-10-10')
            )
        );
    }

    public function test_isDateTimeBetween()
    {
        $this->assertFalse(
            DateUtils::isDateTimeBetween(
                DateUtils::toDateTime('2010-10-10T10:10:10Z'),
                DateUtils::toDateTime('2010-10-10T10:10:11Z'),
                DateUtils::toDateTime('2010-10-10T10:10:12Z')
            )
        );

        $this->assertTrue(
            DateUtils::isDateTimeBetween(
                DateUtils::toDateTime('2010-10-10T10:10:11Z'),
                DateUtils::toDateTime('2010-10-10T10:10:10Z'),
                DateUtils::toDateTime('2010-10-10T10:10:12Z')
            )
        );
    }

    public function testGetDaysDifference()
    {
        $this->assertEquals(0, DateUtils::getDaysDifference('2014-05-16', '2014-05-16'));
        $this->assertEquals(-1, DateUtils::getDaysDifference('2014-05-01', '2014-04-30'));
        $this->assertEquals(4, DateUtils::getDaysDifference('2013-01-01', '2013-01-05'));
        $this->assertEquals(405, DateUtils::getDaysDifference('2012-02-08', '2013-03-19'));
    }

    public function testToIsoString()
    {
        $dateTime = new \DateTime('NOW');

        $this->assertEquals($dateTime->format(DateUtils::FORMAT_ISO_WITH_TIME), DateUtils::toIsoString($dateTime));
    }

    public function testNextDayPreserveTime()
    {
        $this->assertEquals(
            new \DateTime('2005-09-30 03:18:23'),
            DateUtils::nextDay(new \DateTime('2005-09-29 03:18:23'), true)
        );
    }

    /**
     * @dataProvider providerIsToday
     */
    public function testIsToday($isToday, $date)
    {
        $this->assertEquals($isToday, DateUtils::isToday($date));
    }

    public function providerIsToday()
    {
        return [
            [true, new DateTime('now')],
            [false, (new DateTime('now'))->sub(new \DateInterval("P1D"))],
            [false, (new DateTime('now'))->add(new \DateInterval("P1D"))],
            [false, (new DateTime('now'))->add(new \DateInterval("P1Y"))],
            [false, (new DateTime('now'))->sub(new \DateInterval("P1Y"))],
        ];
    }

    public function testNextDayDoNotPreserveTime()
    {
        $this->assertEquals(
            new \DateTime('20-April-2008'),
            DateUtils::nextDay(new \DateTime('2008-04-19 14:23:54'), false)
        );
    }

    public function testCropTime()
    {
        $this->assertEquals(new \DateTime('2007-11-01'), DateUtils::cropTime(new \DateTime('2007-11-01 13:12:43')));
    }

    public function testIsWeekend()
    {
        $this->assertTrue(DateUtils::isWeekend(new \DateTime('2014-05-17')));
        $this->assertTrue(DateUtils::isWeekend(new \DateTime('2014-05-18 00:00:01')));
        $this->assertFalse(DateUtils::isWeekend(new \DateTime('2014-05-16')));
        $this->assertFalse(DateUtils::isWeekend(new \DateTime('2014-05-19 00:00:01')));
    }

    public function testSubtractCalendarMonths()
    {
        $this->assertEquals('2014-03-15', $this->subtractCalendarMonthsTestHelper('2014-04-15', 1));
        $this->assertEquals('2014-01-28', $this->subtractCalendarMonthsTestHelper('2014-02-28', 1));
        $this->assertEquals('2014-03-01', $this->subtractCalendarMonthsTestHelper('2014-04-01', 1));
        $this->assertEquals('2014-02-28', $this->subtractCalendarMonthsTestHelper('2014-03-31', 1));
        $this->assertEquals('2014-11-30', $this->subtractCalendarMonthsTestHelper('2014-12-31', 1));
        $this->assertEquals('2016-06-30', $this->subtractCalendarMonthsTestHelper('2016-07-31', 1));
        $this->assertEquals('2016-12-30', $this->subtractCalendarMonthsTestHelper('2017-01-30', 1));

        $this->assertEquals('2014-01-15', $this->subtractCalendarMonthsTestHelper('2014-04-15', 3));
        $this->assertEquals('2014-02-28', $this->subtractCalendarMonthsTestHelper('2014-04-30', 2));
        $this->assertEquals('2013-11-30', $this->subtractCalendarMonthsTestHelper('2014-01-31', 2));
        $this->assertEquals('2013-10-28', $this->subtractCalendarMonthsTestHelper('2014-02-28', 4));
        $this->assertEquals('2013-02-01', $this->subtractCalendarMonthsTestHelper('2014-04-01', 14));
        $this->assertEquals('2012-02-29', $this->subtractCalendarMonthsTestHelper('2014-03-31', 25));
        $this->assertEquals('2013-11-30', $this->subtractCalendarMonthsTestHelper('2014-12-31', 13));
    }

    public function testValidateDateByParts()
    {
        $this->assertTrue(DateUtils::validateDateByParts('01', '12', '2015'));
    }

    /**
     * @dataProvider providerTestValidateDateByPartsIncorrectDataThrowsIncorrectDateFormatException
     *
     * @expectedException \DvsaCommon\Date\Exception\IncorrectDateFormatException
     */
    public function testValidateDateByPartsIncorrectDataThrowsIncorrectDateFormatException($day, $month, $year)
    {
        DateUtils::validateDateByParts($day, $month, $year);
    }

    public function providerTestValidateDateByPartsIncorrectDataThrowsIncorrectDateFormatException()
    {
        return [
            [29, 2, 2000],
            [29, 2, 2000],
            [1, 12, 2015],
            [29, 2, 2001],
            ['x', 'y', 'z'],
            [31, 9, 2014],
            [0, 12, 2014],
            [30, 0, 2014],
            [30, 12, 32768],
            [31, 12, 0],
            [31, 12, -1],
            [32, 13, 999999],
        ];
    }

    /**
     * @dataProvider providerTestValidateDateByPartsIncorrectDataThrowsNonexistentDateException
     *
     * @expectedException \DvsaCommon\Date\Exception\NonexistentDateException
     */
    public function testValidateDateByPartsIncorrectDataThrowsNonexistentDateException($day, $month, $year)
    {
        DateUtils::validateDateByParts($day, $month, $year);
    }

    public function providerTestValidateDateByPartsIncorrectDataThrowsNonexistentDateException()
    {
        return [
            [32, 12, 2014],
            [-1, 12, 2014],
            [30, 13, 2014],
            [32, 13, 1900],
        ];
    }

    private function subtractCalendarMonthsTestHelper($inputDateStr, $mthNo)
    {
        return DateTimeApiFormat::date(
            DateUtils::subtractCalendarMonths(DateUtils::toDate($inputDateStr), $mthNo)
        );
    }

    public function testFirstOfThisMonth()
    {
        $dateTime = new \DateTime('NOW');
        $month = $dateTime->format('m');
        $year = $dateTime->format('Y');

        $dateTime->setDate($year, $month, 1);
        $dateTime->setTime(0, 0, 0);

        $this->assertEquals($dateTime, DateUtils::firstOfThisMonth());
    }

    /**
     * @dataProvider providerTestGetTimeDifferenceInSeconds
     */
    public function testGetTimeDifferenceInSeconds($time1, $time2, $expectedResult)
    {
        $this->assertEquals($expectedResult, DateUtils::getTimeDifferenceInSeconds($time1, $time2));
    }

    public function providerTestGetTimeDifferenceInSeconds()
    {
        return [
            [new \DateTime('2014-05-18 00:00:01'), new \DateTime('2014-05-18 00:00:01'), 0],
            [new \DateTime('2014-05-18 00:00:10'), new \DateTime('2014-05-18 00:00:05'), 5],
            [new \DateTime('2014-05-18 00:00:05'), new \DateTime('2014-05-18 00:00:10'), 5],
            [new \DateTime('2014-05-18 01:00:00'), new \DateTime('2014-05-18 00:00:00'), 3600],
        ];
    }

    /**
     * @dataProvider providerConvertSecondsToDateInterval
     */
    public function testConvertSecondsToDateInterval($seconds, \DateInterval $expectedResult)
    {
        $result = DateUtils::convertSecondsToDateInterval($seconds);

        $this->assertEquals(
            $expectedResult->format(self::FORMAT_DATE_INTERVAL_COMPARISON),
            $result->format(self::FORMAT_DATE_INTERVAL_COMPARISON)
        );
    }

    /**
     * @dataProvider providerConvertSecondsToMinutesAndSeconds
     */
    public function testConvertSecondsToMinutesAndSeconds($seconds, $expectedResult)
    {
        $result = DateUtils::getMinutesAndSecondsFromSeconds($seconds);

        $this->assertEquals($expectedResult, $result);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_parseDateAndRemoveKeysFromArray_notArrayGiven()
    {
        $data = null;
        DateUtils::concatenateDateStringAndRemoveKeysFromArray($data, 'day', 'month', 'year');
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage year not found in array
     */
    public function test_concatenateDateStringAndRemoveKeysFromArray_keyNotFound()
    {
        $data = ['day' => 1, 'month' => 2, 'YEAR' => 2001];
        DateUtils::concatenateDateStringAndRemoveKeysFromArray($data, 'day', 'month', 'year');
    }

    public function test_concatenateDateStringAndRemoveKeysFromArray_validData()
    {
        $data = ['day' => 1, 'month' => 2, 'year' => 2001];
        $result = DateUtils::concatenateDateStringAndRemoveKeysFromArray($data, 'day', 'month', 'year');

        $this->assertEquals('2001-02-01', $result);
        $this->assertArrayNotHasKey('day', $data);
        $this->assertArrayNotHasKey('month', $data);
        $this->assertArrayNotHasKey('year', $data);
    }

    public function test_concatenateDateStringAndRemoveKeysFromArray_invalidDateGiven_shouldReturnInvalidDateString()
    {
        $data = ['day' => 34, 'month' => 0, 'year' => 19];

        // this method doesn't validate if concatenated string is a valid date
        $result = DateUtils::concatenateDateStringAndRemoveKeysFromArray($data, 'day', 'month', 'year');

        $this->assertEquals('0019-00-34', $result);
        $this->assertArrayNotHasKey('day', $data);
        $this->assertArrayNotHasKey('month', $data);
        $this->assertArrayNotHasKey('year', $data);
    }

    public function providerConvertSecondsToDateInterval()
    {
        return [
            [0, new \DateInterval('PT0S')],
            [1, new \DateInterval('PT1S')],
            [5405, new \DateInterval('PT1H30M5S')],
        ];
    }

    public function providerConvertSecondsToMinutesAndSeconds()
    {
        return [
            [0, ['minutes' => 0, 'seconds' => 0]],
            [1, ['minutes' => 0, 'seconds' => 1]],
            [30, ['minutes' => 0, 'seconds' => 30]],
            [59, ['minutes' => 0, 'seconds' => 59]],
            [60, ['minutes' => 1, 'seconds' => 0]],
            [61, ['minutes' => 1, 'seconds' => 1]],
            [120, ['minutes' => 2, 'seconds' => 0]],
            [3600, ['minutes' => 60, 'seconds' => 0]],
            [3601, ['minutes' => 60, 'seconds' => 1]],
            [7200, ['minutes' => 120, 'seconds' => 0]],
            [60 * 60 * 24 + 23, ['minutes' => 1440, 'seconds' => 23]],
        ];
    }
    public function provideDatesWithDays()
    {
        return [
            ['1970-01-01', '1'],
            ['2014-09-22', '22'],
        ];
    }

    public function provider_toDateTime()
    {
        return [
            ['2004-02-29T12:12:12Z', new \DateTime('2004-02-29T12:12:12')],
            ['2004-02-29T12:12:12+01', new \DateTime('2004-02-29T11:12:12')],
            ['2004-02-29T12:12:12+01:00', new \DateTime('2004-02-29T11:12:12')],
            ['2004-02-29T12:12:12-01', new \DateTime('2004-02-29T13:12:12')],
            ['2004-02-29T12:12:12-01:30', new \DateTime('2004-02-29T13:42:12')],
            ['2004-02-29T12:12:12-1:30', false],
            ['2003-06-12', false],
            ['2005-02-29T24:12:12-01:00', false],
            ['2004-02-29T25:12:12-01:00', false],
            ['2004-02-29T23:60:12-01:00', false],
            ['2004-02-29T23:59:60-01:00', false],
        ];
    }

    /**
     * @dataProvider provider_toDateTime
     */
    public function test_toDateTime($inputStr, $output)
    {
        try {
            $this->assertEquals($output, DateUtils::toDateTime($inputStr));
        } catch (DateException $de) {
            if ($output !== false) {
                $this->fail("DateException expected");
            }
        }
    }


    public function provider_toDate()
    {
        return [
            ['2004-02-29', '2004-02-29'],
            ['2003-06-12', '2003-06-12'],
            ['2005-02-29', false],
            ['2005-01-1', false],
            ['2005-1-01', false],
        ];
    }

    /**
     * @dataProvider provider_toDate
     */
    public function test_toDate($inputStr, $output)
    {
        try {
            $this->assertEquals($output, DateUtils::toDate($inputStr)->format("Y-m-d"));
        } catch (DateException $de) {
            if ($output !== false) {
                $this->fail("DateException expected");
            }
        }
    }


    public function test_toUserTz_summerTime()
    {
        $dateTime = new \DateTime("2001-06-01T22:34:11Z");
        $this->assertEquals("2001-06-01 23:34:11", DateUtils::toUserTz($dateTime)->format("Y-m-d H:i:s"));
    }

    public function test_toUserTz_winterTime()
    {
        $dateTime = new \DateTime("2001-02-01T22:34:11Z");
        $this->assertEquals("2001-02-01 22:34:11", DateUtils::toUserTz($dateTime)->format("Y-m-d H:i:s"));
    }

    public function test_toUserTzTimestamp()
    {
        $dateTime = new \DateTime("2001-02-01T22:34:11Z");
        $this->assertEquals(981066851, DateUtils::toUserTzTimestamp($dateTime));
    }

    public function test_nowAsUserDate()
    {
        $this->assertInstanceOf(\DateTime::class, DateUtils::today());
    }

    public function test_nowAsUserDateTime()
    {
        $this->assertInstanceOf(\DateTime::class, DateUtils::nowAsUserDateTime());
    }

    public function test_toUtc()
    {
        $dateTime = new \DateTime("2001-06-01 22:34:11", new \DateTimeZone("Europe/London"));
        $this->assertEquals('2001-06-01 21:34:11', DateUtils::toUtc($dateTime)->format("Y-m-d H:i:s"));
    }

    public function test_isDateInFuture()
    {
        $dateTime = new \DateTime("1900-06-01 22:34:11", new \DateTimeZone("Europe/London"));
        $this->assertFalse(DateUtils::isDateInFuture($dateTime));
    }

    public function provider_isValidDate()
    {
        return [
            ['2004-02-29', true],
            ['2003-06-12', true],
            ['2005-02-29', false],
            ['2005-01-1', false],
            ['2005-1-01', false],
        ];
    }

    /**
     * @dataProvider provider_isValidDate
     */
    public function test_isValidDate($str, $result)
    {
        $this->assertEquals($result, DateUtils::isValidDate($str));
    }


    public function test_compareDates_eq()
    {
        $this->assertEquals(
            0,
            DateUtils::compareDates(DateUtils::toDate('2004-02-29'), DateUtils::toDate('2004-02-29'))
        );
    }

    public function test_compareDates_gt()
    {
        $this->assertEquals(
            1,
            DateUtils::compareDates(DateUtils::toDate('2004-03-01'), DateUtils::toDate('2004-02-29'))
        );
    }

    public function test_compareDates_lt()
    {
        $this->assertEquals(
            -1,
            DateUtils::compareDates(DateUtils::toDate('2004-02-28'), DateUtils::toDate('2004-02-29'))
        );
    }

    /**
     * @dataProvider dataProviderTestToDateTimeFromParts
     */
    public function testToDateTimeFromParts($parts, $expect)
    {
        if (isset($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        $actual = call_user_func_array(DateUtils::class . '::toDateTimeFromParts', $parts);

        if (isset($expect['result'])) {
            $this->assertEquals($expect['result'], $actual);
        }
    }

    public function testDateRoundUp() {
        $this->assertEquals(new \DateTime('2014-05-06'), DateUtils::roundUp(new \DateTime('2014-05-06 00:00:00')));
        $this->assertEquals(new \DateTime('2014-05-07'), DateUtils::roundUp(new \DateTime('2014-05-06 10:11:25')));
        $this->assertEquals(new \DateTime('2014-05-07'), DateUtils::roundUp(new \DateTime('2014-05-06 23:59:59')));
    }

    public function dataProviderTestToDateTimeFromParts()
    {
        return [
            [
                //  date without time
                'parts'  => [
                    'year'  => '2011',
                    'month' => '2',
                    'day'   => '3',
                ],
                'expect' => [
                    'result' => new \DateTime('2011-02-03'),
                ],
            ],
            [
                //  date without time
                'parts'  => [
                    'year'  => 2011,
                    'month' => 10,
                    'day'   => 9,
                ],
                'expect' => [
                    'result' => new \DateTime('2011-10-09'),
                ],
            ],
            [
                //  date with time
                'parts'  => [
                    'year'   => 2011,
                    'month'  => 10,
                    'day'    => 9,
                    'hour'   => 23,
                    'minute' => 24,
                    'second' => 25,
                ],
                'expect' => [
                    'result' => new \DateTime('2011-10-09 23:24:25'),
                ],
            ],
            [
                //  invalid year
                'parts'  => [
                    'year'   => 11,
                    'month'  => 10,
                    'day'    => 9,
                ],
                'expect' => [
                    'exception' => [
                        'class' => IncorrectDateFormatException::class,
                        'message' => 'wrong params length or type',
                    ],
                ],
            ],
            [
                //  invalid month
                'parts'  => [
                    'year'   => 2011,
                    'month'  => 'a',
                    'day'    => 9,
                ],
                'expect' => [
                    'exception' => [
                        'class' => IncorrectDateFormatException::class,
                        'message' => 'wrong params length or type',
                    ],
                ],
            ],
            [
                //  invalid hour
                'parts'  => [
                    'year'   => 2011,
                    'month'  => 10,
                    'day'    => 9,
                    'hour'   => 'b',
                ],
                'expect' => [
                    'exception' => [
                        'class' => IncorrectDateFormatException::class,
                        'message' => 'wrong params length or type',
                    ],
                ],
            ],
        ];
    }
}
