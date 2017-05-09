<?php

namespace DvsaMotEnforcementTest\Decorator;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaMotEnforcement\Decorator\ElapsedMileageFormatter;
use PHPUnit_Framework_TestCase;

/**
 * Class ReinspectionReportDecoratorTest.
 */
class ElapsedMileageFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testFormattingElapsedMileage(
        OdometerReadingDto $odometerFromTester,
        OdometerReadingDto $odometerFromExaminer,
        $expectedPrint
    ) {
        //given

        //when
        $actualPrint = ElapsedMileageFormatter::formatElapsedMileage($odometerFromTester, $odometerFromExaminer);

        //then
        $this->assertEquals($expectedPrint, $actualPrint);
    }

    public function dataProvider()
    {
        /**
         * [
         *   odometerFromTester,
         *   odometerFromExaminer,
         *   expected print
         * ],.
         */
        $dataSet = [
            [
                OdometerReadingDto::create()
                    ->setValue('120')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDto::create()
                    ->setValue('125')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '5 mi (T:120 mi, VE:125 mi)',
            ],
            [
                OdometerReadingDto::create()
                    ->setValue('125')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDto::create()
                    ->setValue('120')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '5 mi (T:125 mi, VE:120 mi)',
            ],
            [
                OdometerReadingDto::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDto::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '0 mi (T:123 mi, VE:123 mi)',
            ],
            [
                OdometerReadingDto::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDto::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::KILOMETERS)
                    ->setResultType(OdometerReadingResultType::OK),
                'Diff. units (T:123 mi, VE:123 km)',
            ],
            [
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                'No odometer (T:No odometer, VE:No odometer)',
            ],
            [
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::NOT_READABLE),
                'Diff. readings (T:No odometer, VE:Odometer not readable)',
            ],
            [
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setUnit(OdometerUnit::MILES)
                    ->setValue('123'),
                'Diff. readings (T:No odometer, VE:123 mi)',
            ],
            [
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setValue('123'),
                OdometerReadingDto::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setUnit(OdometerUnit::MILES),
                'Diff. units (T:n/a, VE:n/a)',
            ],
            [
                OdometerReadingDto::create()
                    ->setValue('123'),
                OdometerReadingDto::create()
                    ->setValue('123'),
                'n/a (T:n/a, VE:n/a)',
            ],
        ];

        return $dataSet;
    }
}
