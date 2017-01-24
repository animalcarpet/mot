<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck\StatisticsParameterCheck;
use DvsaCommon\Enum\VehicleClassGroupCode;

class GroupStatisticsParameterCheck
{
    private $nationalStatisticsParameterCheck;

    public function __construct()
    {
        $this->nationalStatisticsParameterCheck = new StatisticsParameterCheck();
    }

    public function isValid($year, $month, $group)
    {
        $isValid = $this->nationalStatisticsParameterCheck->isValid($year, $month);

        return ($isValid && VehicleClassGroupCode::exists($group));
    }
}
