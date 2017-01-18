<?php

namespace DvsaMotEnforcement\Decorator;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\OdometerReadingDto;

class ElapsedMileageFormatter
{
    /**
     * Given the original test and the re-inspection, return a string containing the difference
     * between the readings.
     *
     * @param $odometerFromTester
     * @param $odometerFromExaminer
     *
     * @return string
     */
    public static function formatElapsedMileage(
        OdometerReadingDto $odometerFromTester,
        OdometerReadingDto $odometerFromExaminer
    ) {
        if (self::bothSameNonNumeric($odometerFromTester->getResultType(), $odometerFromExaminer->getResultType())) {
            $differenceMsg = self::formattedMileage($odometerFromTester);
        } elseif (self::bothNumeric($odometerFromTester->getResultType(), $odometerFromExaminer->getResultType())) {
            if ($odometerFromTester->getUnit() === $odometerFromExaminer->getUnit()) {
                $differenceMsg = sprintf(
                    '%s %s',
                    abs($odometerFromTester->getValue() - $odometerFromExaminer->getValue()),
                    $odometerFromTester->getUnit()
                );
            } else {
                $differenceMsg = 'Diff. units';
            }
        } else {
            $differenceMsg = 'Diff. readings';
        }

        return sprintf(
            '%s (T:%s, VE:%s)',
            $differenceMsg,
            self::formattedMileage($odometerFromTester),
            self::formattedMileage($odometerFromExaminer)
        );
    }

    private static function bothNumeric($readingType1, $readingType2)
    {
        return $readingType1 === $readingType2 && $readingType1 === OdometerReadingResultType::OK;
    }

    private static function bothSameNonNumeric($readingType1, $readingType2)
    {
        return $readingType1 === $readingType2 && $readingType1 !== OdometerReadingResultType::OK;
    }

    private static function formattedMileage(OdometerReadingDto $odometer)
    {
        switch ($odometer->getResultType()) {
            case OdometerReadingResultType::OK:
                return (is_null($odometer->getValue()) || is_null($odometer->getUnit()))
                    ? 'n/a' : $odometer->getValue() . ' ' . $odometer->getUnit();

            case OdometerReadingResultType::NO_ODOMETER:
                return 'No odometer';

            case OdometerReadingResultType::NOT_READABLE:
                return 'Odometer not readable';

            default:
                return 'n/a';
        }
    }
}
