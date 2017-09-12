<?php
namespace SiteTest\Csv\TQI;

use Site\Csv\TQI\TableHeadersTqiCsvSection;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;

class TableHeadersTqiCsvSectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_build_returnsArray()
    {
        $csv = new TableHeadersTqiCsvSection($this->getEmployeePerfromanceCollection(), 5);
        $matrix = $csv->build();

        $this->assertEquals($this->getExpectedMatrix(), $matrix);
    }


    private function getEmployeePerfromanceCollection(): array
    {
        $john = new EmployeePerformanceDto();
        $john->setFirstName("John");
        $john->setFamilyName("Smith");
        $john->setUsername("js");

        $brendan = new EmployeePerformanceDto();
        $brendan->setFirstName("Brendan");
        $brendan->setFamilyName("Kainos");
        $brendan->setUsername("bk");

        return [$john, $brendan];
    }

    private function getExpectedMatrix(): array
    {
        return [
            0 => [
                0 => "",
                1 => "John Smith",
                2 => "Brendan Kainos",
                3 => TableHeadersTqiCsvSection::SITE_AVERAGE_COLUMN_NAME,
                4 => TableHeadersTqiCsvSection::NATIONAL_AVERAGE_COLUMN_NAME,
            ],
            1 => [
                0 => "",
                1 => "js",
                2 => "bk",
                3 => "",
                4 => "",
            ],
            2 => [
                0 => "",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
        ];
    }
}