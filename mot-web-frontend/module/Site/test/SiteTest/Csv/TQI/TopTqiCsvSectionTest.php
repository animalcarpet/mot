<?php
namespace SiteTest\Csv\TQI;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Site\Csv\TQI\TopTqiCsvSection;
use Site\ViewModel\TestQuality\UserTestQualityViewModel;

class TopTqiCsvSectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_build_returnsArray()
    {
        $vts = new VehicleTestingStationDto();
        $vts->setSiteNumber("num. 7");
        $vts->setName("Garage num. 7");


        $lastMonthsDateRange = new LastMonthsDateRange(new DateTimeHolder());
        $lastMonthsDateRange->setNumberOfMonths($lastMonthsDateRange::ONE_MONTH);

        $csv = new TopTqiCsvSection(
            $vts,
            $lastMonthsDateRange,
            VehicleClassGroupCode::BIKES,
            2
        );

        $matrix = $csv->build();

        $this->assertEquals($this->getExpectedMatrix($vts, $lastMonthsDateRange), $matrix);
    }

    private function getExpectedMatrix(VehicleTestingStationDto $vts, LastMonthsDateRange $lastMonthsDateRange): array
    {
        $classString = UserTestQualityViewModel::$subtitles[VehicleClassGroupCode::BIKES];

        return [
            0 => [0 => $vts->getName(), 1 => ""],
            1 => [0 => $vts->getSiteNumber(), 1 => ""],
            2 => [0 => $lastMonthsDateRange->getStartDate()->format(TopTqiCsvSection::CSV_DATE_FORMAT), 1 => ""],
            3 => [0 => "", 1 => ""],
            4 => [0 => "Group " . VehicleClassGroupCode::BIKES, 1 => ""],
            5 => [0 => $classString, 1 => ""],
            6 => [0 => "", 1 => ""],

        ];
    }
}