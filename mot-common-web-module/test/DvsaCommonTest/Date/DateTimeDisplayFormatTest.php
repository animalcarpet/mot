<?php

namespace DvsaCommonTest\Date;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\Time;

/**
 *
 */
class DateTimeDisplayFormatTest extends \PHPUnit_Framework_TestCase
{

    public function provider_dateTime()
    {
        return [
            ['2014-05-05T12:30:59Z', '5 May 2014, 1:30pm'],
            ['2014-05-05T12:30:59Z', '5 May 2014, 1:30pm'],
            ['2014-05-05T23:30:59Z', '6 May 2014, 12:30am'],
            [null, ''],
        ];
    }

    /**
     * @dataProvider provider_dateTime
     */
    public function test_dateTime($dateTime, $expected)
    {
        $this->assertEquals(
            $expected,
            DateTimeDisplayFormat::dateTime(is_string($dateTime) ? new \DateTime($dateTime) : null)
        );
    }

    /**
     * @dataProvider provider_dateTime
     */
    public function test_textDateTime($dateTime, $expected)
    {
        $this->assertEquals($expected, DateTimeDisplayFormat::textDateTime($dateTime));
    }

    public function provider_dateTimeShort()
    {
        return [
            ['2014-09-05T12:30:59Z', '5 Sep 2014, 1:30pm'],
            ['2014-10-05T12:30:59Z', '5 Oct 2014, 1:30pm'],
            ['2014-08-05T23:30:59Z', '6 Aug 2014, 12:30am'],
            [null, ''],
        ];
    }

    /**
     * @dataProvider provider_dateTimeShort
     */
    public function test_dateTimeShort($dateTime, $expected)
    {
        $this->assertEquals(
            $expected,
            DateTimeDisplayFormat::dateTimeShort(is_string($dateTime) ? new \DateTime($dateTime) : null)
        );
    }

    /**
     * @dataProvider provider_dateTimeShort
     */
    public function test_textDateTimeShort($dateTime, $expected)
    {
        $this->assertEquals($expected, DateTimeDisplayFormat::textDateTimeShort($dateTime));
    }

    public function provider_date()
    {
        return [
            ['2014-05-05', '5 May 2014'],
            [null, '']
        ];
    }

    /**
     * @dataProvider provider_date
     */
    public function test_date($date, $expected)
    {
        $this->assertEquals(
            $expected,
            DateTimeDisplayFormat::date(is_string($date) ? new \DateTime($date) : null)
        );
    }

    /**
     * @dataProvider provider_date
     */
    public function test_textDate($date, $expected)
    {
        $this->assertEquals(
            $expected,
            DateTimeDisplayFormat::textDate($date)
        );
    }

    public function provider_time()
    {
        return [
            [(new \DateTime("2003-06-01"))->setTime(3, 5, 33), '4:05am'],
            [(new \DateTime("2003-06-01"))->setTime(13, 5, 33), '2:05pm'],
            [(new \DateTime("2003-02-01"))->setTime(13, 5, 33), '1:05pm'],
            [Time::fromDateTime((new \DateTime)->setTime(13, 5, 33)), '1:05pm'],
            [null, '']
        ];
    }

    /**
     * @dataProvider provider_time
     */
    public function test_time($time, $expected)
    {
        $this->assertEquals(
            $expected,
            DateTimeDisplayFormat::time($time)
        );
    }

    public function test_nowAsDate()
    {
        $this->assertInternalType('string', DateTimeDisplayFormat::nowAsDate());
    }

    public function test_nowAsDateTime()
    {
        $this->assertInternalType('string', DateTimeDisplayFormat::nowAsDateTime());
    }

    /**
     * @dataProvider provider_shortGds
     */
    public function test_dateShortGds($dateString, $date)
    {
        $this->assertEquals($dateString, DateTimeDisplayFormat::textDateShortGds($date));
    }

    public function provider_shortGds()
    {
        $now = new \DateTime();
        $format = 'Y-m-d';
        return [
            ['Today', $now->format($format)],
            ['5 Jan 2013', "2013-01-05"],
            ['1 Jan 2016', "2016-01-01"],
            ['31 Dec 2016', "2016-12-31"],
        ];
    }
}
