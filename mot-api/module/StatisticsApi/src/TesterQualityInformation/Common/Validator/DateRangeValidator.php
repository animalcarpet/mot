<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator;

use DvsaCommon\Date\LastMonthsDateRange;

class DateRangeValidator
{
    const DATE_RANGE = [LastMonthsDateRange::ONE_MONTH, LastMonthsDateRange::THREE_MONTHS];

    public function isValid($dateRange)
    {
        if (!in_array($dateRange, self::DATE_RANGE)) {
            return false;
        }

        return true;
    }
}
