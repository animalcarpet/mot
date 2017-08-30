<?php

namespace DvsaCommonTest\Date;

use DvsaCommon\Date\LastMonthsDateRange;

class LastMonthsDateRangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testRangeOneMonthDataProvider
     */
    public function testDateRange($monthRange, $currentDate, $startDate, $endDate)
    {
        $lastMonthsDateRange = new LastMonthsDateRange(new TestDateTimeHolder($currentDate));
        $lastMonthsDateRange->setNumberOfMonths($monthRange);

        $this->assertEquals($startDate, $lastMonthsDateRange->getStartDate());
        $this->assertEquals($endDate, $lastMonthsDateRange->getEndDate());
    }

    /**
     * @dataProvider testToStringDataProvider
     */
    public function testToString($monthRange, $currentDate, $expectedWording)
    {
        $lastMonthsDateRange = new LastMonthsDateRange(new TestDateTimeHolder($currentDate));
        $lastMonthsDateRange->setNumberOfMonths($monthRange);

        $this->assertSame($expectedWording, $lastMonthsDateRange->__toString());
    }

    public function testRangeOneMonthDataProvider():array
    {
        return [
            [
                1,
                new \DateTime('31-01-2017'),
                new \DateTime('01-12-2016 00:00:00'),
                new \DateTime('31-12-2016 23:59:59'),
            ],
            [
                1,
                new \DateTime('15-02-2017'),
                new \DateTime('01-01-2017 00:00:00'),
                new \DateTime('31-01-2017 23:59:59'),
            ],
            [
                3,
                new \DateTime('15-02-2017'),
                new \DateTime('01-11-2016 00:00:00'),
                new \DateTime('31-01-2017 23:59:59'),
            ],
        ];
    }

    public function testToStringDataProvider():array
    {
        return [
            [
                1,
                new \DateTime('11-03-2017'),
                'February 2017'
            ],
            [
                3,
                new \DateTime('11-03-2017'),
                'December 2016 to February 2017'
            ],
            [
                3,
                new \DateTime('15-06-2017'),
                'March to May 2017'
            ],
        ];
    }
}