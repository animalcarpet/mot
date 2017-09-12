<?php
namespace SiteTest\Csv\TQI;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use Site\Csv\TQI\InitialTestPerformanceTqiCsvSection;

class InitialTestPerformanceTqiCsvSectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_build_returnsCorrectArrayWhenNationalDataAreAvailable()
    {
        $csv = new InitialTestPerformanceTqiCsvSection(
            $this->getEmployeePerfromanceCollection(),
            $this->getSiteAverage(),
            true,
            $this->getNationalAverage(),
            5
        );

        $matrix = $csv->build();

        $this->assertEquals($this->getExpectedMatrixForAvailableNatinalData(), $matrix);
    }

    private function getExpectedMatrixForAvailableNatinalData(): array
    {
        return [
            0 => [
                0 => "Initial test performance",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
            1 => [
                0 => "Tests done",
                1 => "451",
                2 => "2",
                3 => "3333",
                4 => "8585",
            ],
            2 => [
                0 => "Average vehicle age",
                1 => "3",
                2 => "0",
                3 => "1",
                4 => "5",
            ],
            3 => [
                0 => "Average test time",
                1 => "29",
                2 => "57",
                3 => "10",
                4 => "22",
            ],
            4 => [
                0 => "Tests failed",
                1 => "19%",
                2 => "68%",
                3 => "33%",
                4 => "13%",
            ],
            5 => [
                0 => "",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
        ];
    }

    public function test_build_returnsCorrectArrayWhenNationalDataAreNotAvailable()
    {
        $csv = new InitialTestPerformanceTqiCsvSection(
            $this->getEmployeePerfromanceCollection(),
            $this->getSiteAverage(),
            false,
            $this->getNationalAverage(),
            5
        );

        $matrix = $csv->build();

        $this->assertEquals($this->getExpectedMatrixForNotAvailableNatinalData(), $matrix);
    }

    private function getExpectedMatrixForNotAvailableNatinalData(): array
    {
        return [
            0 => [
                0 => "Initial test performance",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
            1 => [
                0 => "Tests done",
                1 => "451",
                2 => "2",
                3 => "3333",
                4 => "0",
            ],
            2 => [
                0 => "Average vehicle age",
                1 => "3",
                2 => "0",
                3 => "1",
                4 => "0",
            ],
            3 => [
                0 => "Average test time",
                1 => "29",
                2 => "57",
                3 => "10",
                4 => "0",
            ],
            4 => [
                0 => "Tests failed",
                1 => "19%",
                2 => "68%",
                3 => "33%",
                4 => "0%",
            ],
            5 => [
                0 => "",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
        ];
    }



    private function getEmployeePerfromanceCollection(): array
    {
        $john = new EmployeePerformanceDto();
        $john->setFirstName("John");
        $john->setFamilyName("Smith");
        $john->setUsername("js");
        $john->setIsAverageVehicleAgeAvailable(true);
        $john->setAverageVehicleAgeInMonths(33);
        $john->setTotal(451);
        $john->setAverageTime(new TimeSpan(0, 0, 29, 0));
        $john->setPercentageFailed(18.93);

        $brendan = new EmployeePerformanceDto();
        $brendan->setFirstName("Brendan");
        $brendan->setFamilyName("Kainos");
        $brendan->setUsername("bk");
        $brendan->setIsAverageVehicleAgeAvailable(false);
        $brendan->setTotal(2);
        $brendan->setAverageTime(new TimeSpan(0, 0, 57, 0));
        $brendan->setPercentageFailed(68.00);

        return [$john, $brendan];
    }

    private function getSiteAverage(): MotTestingPerformanceDto
    {
        $siteAverage = new MotTestingPerformanceDto();
        $siteAverage->setIsAverageVehicleAgeAvailable(true);
        $siteAverage->setAverageVehicleAgeInMonths(12);
        $siteAverage->setAverageTime(new TimeSpan(0, 0, 10, 0));
        $siteAverage->setPercentageFailed(33.33);
        $siteAverage->setTotal(3333);

        return $siteAverage;
    }

    private function getNationalAverage(): MotTestingPerformanceDto
    {
        $nationalAverage = new MotTestingPerformanceDto();
        $nationalAverage->setIsAverageVehicleAgeAvailable(true);
        $nationalAverage->setAverageVehicleAgeInMonths(54);
        $nationalAverage->setAverageTime(new TimeSpan(0, 0, 22, 0));
        $nationalAverage->setPercentageFailed(13.31);
        $nationalAverage->setTotal(8585);

        return $nationalAverage;
    }

}