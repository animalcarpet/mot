<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Storage;

use DvsaCommon\Enum\VehicleClassGroupCode;

/**
 * Class S3KeyGenerator.
 */
class S3KeyGenerator
{
    const NATIONAL_TESTER_STATISTICS_FOLDER = 'tester-quality-information/tester-performance/national';
    const NATIONAL_TESTER_STATISTICS_FILE_NAME_TEMPLATE = '%s/%s-%s-%s.json';

    const NATIONAL_COMPONENT_BREAKDOWN_GROUP_A_FOLDER
        = 'tester-quality-information/component-fail-rate/national/group-A';
    const NATIONAL_COMPONENT_BREAKDOWN_GROUP_B_FOLDER
        = 'tester-quality-information/component-fail-rate/national/group-B';
    const NATIONAL_COMPONENT_BREAKDOWN_FILE_NAME_TEMPLATE = '%s/%s-%s-%s.json';

    const SITE_TESTER_STATISTICS_FOLDER = 'tester-quality-information/tester-performance/site';
    const SITE_TESTER_STATISTICS_FILE_NAME_TEMPLATE = '%s/%s/%s-%s.json';

    public function generateForNationalTesterStatistics(int $year, int $month, int $monthRange):string
    {
        $year = (string) $year;
        $month = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        return sprintf(
            self::NATIONAL_TESTER_STATISTICS_FILE_NAME_TEMPLATE,
            self::NATIONAL_TESTER_STATISTICS_FOLDER, $year, $month, $monthRange);
    }

    public function generateForSiteTesterStatistics($siteId, $year, $month):string
    {
        $year = (string) (int) $year;
        $month = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);

        return sprintf(self::SITE_TESTER_STATISTICS_FILE_NAME_TEMPLATE, self::SITE_TESTER_STATISTICS_FOLDER, $siteId, $year, $month);
    }

    public function generateForComponentBreakdownStatistics(int $year, int $month, string $vehicleGroup, int $monthRange):string
    {
        $year = (string) $year;
        $month = str_pad((string) $month, 2, '0', STR_PAD_LEFT);

        $folder = $this->getComponentBreakdownFolderForGroup($vehicleGroup);

        return sprintf(self::NATIONAL_COMPONENT_BREAKDOWN_FILE_NAME_TEMPLATE, $folder, $year, $month, $monthRange);
    }

    public function getComponentBreakdownFolderForGroup($vehicleGroup):string
    {
        return $vehicleGroup == VehicleClassGroupCode::BIKES
            ? self::NATIONAL_COMPONENT_BREAKDOWN_GROUP_A_FOLDER
            : self::NATIONAL_COMPONENT_BREAKDOWN_GROUP_B_FOLDER;
    }
}
