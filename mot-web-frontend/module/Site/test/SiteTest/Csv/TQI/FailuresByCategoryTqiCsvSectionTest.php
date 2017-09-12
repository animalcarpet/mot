<?php
namespace SiteTest\Csv\TQI;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Site\Csv\TQI\FailuresByCategoryTqiCsvSection;

class FailuresByCategoryTqiCsvSectionTest extends \PHPUnit_Framework_TestCase
{
    public function test_build_returnsArray()
    {
        $csv = new FailuresByCategoryTqiCsvSection(
            $this->getNationalComponentStatistics(),
            $this->getSiteComponentStatistics(),
            $this->getComponentBreakdown(),
            5
        );

        $matrix = $csv->build();

        $this->assertEquals($this->getExpectedMatrix(), $matrix);
    }

    private function getExpectedMatrix(): array
    {
        return [
            0 => [
                0 => "Failures by category",
                1 => "",
                2 => "",
                3 => "",
                4 => "",
            ],
            1 => [
                0 => "Wheel",
                1 => "19.4%",
                2 => "33.1%",
                3 => "23.2%",
                4 => "",
            ],
            2 => [
                0 => "engine",
                1 => "4.7%",
                2 => "12.9%",
                3 => "1.0%",
                4 => "",
            ]
        ];
    }

    private function getNationalComponentStatistics(): NationalComponentStatisticsDto
    {
        $dto = new NationalComponentStatisticsDto();
        $dto->setGroup(VehicleClassGroupCode::CARS_ETC);
        $dto->setMonthRange(LastMonthsDateRange::ONE_MONTH);
        $dto->setComponents([$this->getWheelComponent(), $this->getEngineComponent()]);

        return $dto;
    }

    private function getSiteComponentStatistics(): SiteComponentStatisticsDto
    {
        $wheel = $this->getWheelComponent();
        $wheel->setPercentageFailed(33.12);

        $engine = $this->getEngineComponent();
        $engine->setPercentageFailed(12.87);

        $dto = new SiteComponentStatisticsDto();
        $dto->setGroup(VehicleClassGroupCode::CARS_ETC);
        $dto->setMonthRange(LastMonthsDateRange::ONE_MONTH);
        $dto->setSiteId(123);
        $dto->setComponents([$wheel, $engine]);

        return $dto;
    }

    private function getComponentBreakdown()
    {
        $motTestingPerformance = new MotTestingPerformanceDto();
        $motTestingPerformance->setIsAverageVehicleAgeAvailable(true);
        $motTestingPerformance->setAverageVehicleAgeInMonths(54);
        $motTestingPerformance->setAverageTime(new TimeSpan(0, 0, 22, 0));
        $motTestingPerformance->setPercentageFailed(13.31);
        $motTestingPerformance->setTotal(8585);

        $wheel = $this->getWheelComponent();
        $wheel->setPercentageFailed(19.44);

        $engine = $this->getEngineComponent();
        $engine->setPercentageFailed(4.65);

        $dto = new ComponentBreakdownDto();
        $dto->setSiteName("Garage");
        $dto->setDisplayName("John Smith");
        $dto->setUserName("jogn_smith");
        $dto->setGroupPerformance($motTestingPerformance);
        $dto->setComponents([$engine, $wheel]);

        return [$dto];
    }

    private function getWheelComponent(): ComponentDto
    {
        $wheelComponent = new ComponentDto();
        $wheelComponent->setId(1);
        $wheelComponent->setPercentageFailed(23.22);
        $wheelComponent->setName("Wheel");

        return $wheelComponent;
    }

    private function getEngineComponent(): ComponentDto
    {
        $engineComponent = new ComponentDto();
        $engineComponent->setId(2);
        $engineComponent->setPercentageFailed(0.99);
        $engineComponent->setName("engine");

        return $engineComponent;
    }
}