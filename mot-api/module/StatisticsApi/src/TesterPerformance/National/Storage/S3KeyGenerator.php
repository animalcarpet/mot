<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage;

use DvsaCommon\Enum\VehicleClassGroupCode;

/**
 * Class S3KeyGenerator
 * @package Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Storage
 *
 * Generates keys for TQI tester performance reports stored in S3
 */
class S3KeyGenerator
{
    const NATIONAL_TESTER_STATISTICS_FOLDER = 'tester-quality-information/tester-performance/national';
    const NATIONAL_TESTER_STATISTICS_FILE_NAME_TEMPLATE = '%s/%s-%s.json';

    const NATIONAL_COMPONENT_BREAKDOWN_GROUP_A_FOLDER
        = 'tester-quality-information/component-fail-rate/national/group-A';
    const NATIONAL_COMPONENT_BREAKDOWN_GROUP_B_FOLDER
        = 'tester-quality-information/component-fail-rate/national/group-B';
    const NATIONAL_COMPONENT_BREAKDOWN_FILE_NAME_TEMPLATE = '%s/%s-%s.json';

    const SITE_TESTER_STATISTICS_FOLDER = 'tester-quality-information/tester-performance/site';
    const SITE_TESTER_STATISTICS_FILE_NAME_TEMPLATE = '%s/%s/%s-%s.json';

    public function generateForNationalTesterStatistics($year, $month)
    {
        $year = (string)(int)$year;
        $month = str_pad((string)(int)$month, 2, '0', STR_PAD_LEFT);
        return sprintf(self::NATIONAL_TESTER_STATISTICS_FILE_NAME_TEMPLATE, self::NATIONAL_TESTER_STATISTICS_FOLDER, $year, $month);
    }

    public function generateForSiteTesterStatistics($siteId, $year, $month)
    {
        $year = (string)(int)$year;
        $month = str_pad((string)(int)$month, 2, '0', STR_PAD_LEFT);
        return sprintf(self::SITE_TESTER_STATISTICS_FILE_NAME_TEMPLATE, self::SITE_TESTER_STATISTICS_FOLDER, $siteId, $year, $month);
    }

    public function generateForComponentBreakdownStatistics($year, $month, $vehicleGroup)
    {
        $year = (string)(int)$year;
        $month = str_pad((string)(int)$month, 2, '0', STR_PAD_LEFT);

        $folder = $this->getComponentBreakdownFolderForGroup($vehicleGroup);

        return sprintf(self::NATIONAL_TESTER_STATISTICS_FILE_NAME_TEMPLATE, $folder, $year, $month);
    }

    public function getComponentBreakdownFolderForGroup($vehicleGroup)
    {
        return $vehicleGroup == VehicleClassGroupCode::BIKES
            ? self::NATIONAL_COMPONENT_BREAKDOWN_GROUP_A_FOLDER
            : self::NATIONAL_COMPONENT_BREAKDOWN_GROUP_B_FOLDER;
    }
}
