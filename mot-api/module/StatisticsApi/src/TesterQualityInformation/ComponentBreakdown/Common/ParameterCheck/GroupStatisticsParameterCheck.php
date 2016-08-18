<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck\StatisticsParameterCheck;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\VehicleClassGroupCode;

class GroupStatisticsParameterCheck
{
    private $nationalStatisticsParameterCheck;

    public function __construct(DateTimeHolder $dateTimeHolder)
    {
        $this->nationalStatisticsParameterCheck = new StatisticsParameterCheck($dateTimeHolder);
    }

    public function isValid($year, $month, $group)
    {
        $isValid = $this->nationalStatisticsParameterCheck->isValid($year, $month);

        return ($isValid && VehicleClassGroupCode::exists($group));
    }
}
