<?php

namespace DvsaCommonTest\UrlBuilder;

use DvsaCommon\UrlBuilder\AbstractUrlBuilder;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;

class MotTestUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = 12332;
    const USERNAME = 'tester1';
    const MOT_TEST_ID = 77777;
    const MOT_TEST_NR = '123456798';
    const RFR_ID = 888899;
    const V5C = 'unit_V5c';

    public function test()
    {
        $base = 'mot-test/' . self::MOT_TEST_NR;
        $this->checkUrl(MotTestUrlBuilder::motTest(self::MOT_TEST_NR), $base);

        $this->checkUrl(MotTestUrlBuilder::minimal(self::MOT_TEST_NR), $base . '/minimal');
        $this->checkUrl(MotTestUrlBuilder::motTestStatus(self::MOT_TEST_NR), $base . '/status');
        $this->checkUrl(
            MotTestUrlBuilder::reasonForRejection(self::MOT_TEST_NR, self::RFR_ID),
            $base . '/reasons-for-rejection/' . self::RFR_ID
        );

        $odometerBase = $base . '/odometer-reading';
        $this->checkUrl(MotTestUrlBuilder::odometerReading(self::MOT_TEST_NR), $odometerBase);
        $this->checkUrl(
            MotTestUrlBuilder::odometerReadingModifyCheck(self::MOT_TEST_NR),
            $odometerBase . '/modify-check'
        );
        $this->checkUrl(MotTestUrlBuilder::odometerReadingNotices(self::MOT_TEST_NR), $odometerBase . '/notices');

        $this->checkUrl(MotTestUrlBuilder::retest(), 'mot-retest');
        $this->checkUrl(MotTestUrlBuilder::demoTest(), 'mot-demo-test');

        $this->checkUrl(MotTestUrlBuilder::search(), 'mot-test-search');

        $this->checkUrl(MotTestUrlBuilder::refusal(), 'mot-test-refusal');
    }

    private function checkUrl(AbstractUrlBuilder $urlBuilder, $expectUrl)
    {
        $this->assertEquals($expectUrl, $urlBuilder->toString());
        $this->assertInstanceOf(MotTestUrlBuilder::class, $urlBuilder);
    }
}
