<?php
namespace DvsaMotEnforcementTest\Decorator;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Dto\Common\OdometerReadingDTO;
use DvsaMotEnforcement\Decorator\ElapsedMileageFormatter;
use PHPUnit_Framework_TestCase;

/**
 * Class ReinspectionReportDecoratorTest
 *
 * @package DvsaMotEnforcementTest\Decorator
 */
class ElapsedMileageFormatterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testFormattingElapsedMileage(
        OdometerReadingDTO $odometerFromTester,
        OdometerReadingDTO $odometerFromExaminer,
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
         * ],
         */
        $dataSet = [
            [
                OdometerReadingDTO::create()
                    ->setValue('120')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDTO::create()
                    ->setValue('125')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '5 mi (T:120 mi, VE:125 mi)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setValue('125')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDTO::create()
                    ->setValue('120')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '5 mi (T:125 mi, VE:120 mi)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDTO::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                '0 mi (T:123 mi, VE:123 mi)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::MILES)
                    ->setResultType(OdometerReadingResultType::OK),
                OdometerReadingDTO::create()
                    ->setValue('123')
                    ->setUnit(OdometerUnit::KILOMETERS)
                    ->setResultType(OdometerReadingResultType::OK),
                'Diff. units (T:123 mi, VE:123 km)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                'No odometer (T:No odometer, VE:No odometer)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::NOT_READABLE),
                'Diff. readings (T:No odometer, VE:Odometer not readable)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::NO_ODOMETER),
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setUnit(OdometerUnit::MILES)
                    ->setValue('123'),
                'Diff. readings (T:No odometer, VE:123 mi)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setValue('123'),
                OdometerReadingDTO::create()
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setUnit(OdometerUnit::MILES),
                'Diff. units (T:n/a, VE:n/a)'
            ],
            [
                OdometerReadingDTO::create()
                    ->setValue('123'),
                OdometerReadingDTO::create()
                    ->setValue('123'),
                'n/a (T:n/a, VE:n/a)'
            ],
        ];

        return $dataSet;
    }

}
