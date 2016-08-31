<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryResult\AbstractTesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\QueryResult\TesterMultiSitePerformanceResult;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Mapper\AddressMapper;

class TesterStatisticsMapper
{
    /**
     * @param TesterPerformanceResult[] $statistics
     * @return SitePerformanceDto
     */
    public function buildSitePerformanceDto(array $statistics)
    {
        $siteDto = new SitePerformanceDto();

        $groupA = $this->createSiteGroupPerformanceDtoForClassGroup($statistics, VehicleClassGroupCode::BIKES);
        $siteDto->setA($groupA);

        $groupB = $this->createSiteGroupPerformanceDtoForClassGroup($statistics, VehicleClassGroupCode::CARS_ETC);
        $siteDto->setB($groupB);

        return $siteDto;
    }

    /**§
     * @param TesterPerformanceResult[] $statistics
     * @return TesterPerformanceDto
     */
    public function buildTesterPerformanceDto(array $statistics)
    {
        $dto = new TesterPerformanceDto();

        $groupAStatistics = $this->filterStatisticsByGroup($statistics, VehicleClassGroupCode::BIKES);
        $groupBStatistics = $this->filterStatisticsByGroup($statistics, VehicleClassGroupCode::CARS_ETC);

        if (count($groupAStatistics) > 1 || count($groupBStatistics) > 1) {
            throw new \InvalidArgumentException();
        }

        $groupATesterPerformanceResult = reset($groupAStatistics);
        if (!empty($groupATesterPerformanceResult)) {
            $groupAPerformance = $this->buildEmployeePerformanceDto($groupATesterPerformanceResult);
            $dto->setGroupAPerformance($groupAPerformance);
        }

        $groupBTesterPerformanceResult = reset($groupBStatistics);
        if (!empty($groupBTesterPerformanceResult)) {
            $groupBPerformance = $this->buildEmployeePerformanceDto($groupBTesterPerformanceResult);
            $dto->setGroupBPerformance($groupBPerformance);
        }

        return $dto;
    }

    /**
     * @param array $statistics
     * @param $vehicleGroup
     * @return AbstractTesterPerformanceResult[]
     */
    private function filterStatisticsByGroup(array $statistics, $vehicleGroup)
    {
        /** @var AbstractTesterPerformanceResult[] $groupStatistics */
        $groupStatistics = ArrayUtils::filter($statistics, function (AbstractTesterPerformanceResult $statistic) use ($vehicleGroup) {
            return $statistic->getVehicleClassGroup() == $vehicleGroup;
        });

        return $groupStatistics;
    }

    /**
     * @param TesterPerformanceResult[] $statistics
     * @param string $vehicleGroup
     * @return SiteGroupPerformanceDto
     */
    private function createSiteGroupPerformanceDtoForClassGroup(array $statistics, $vehicleGroup)
    {
        $groupStatistics = $this->filterStatisticsByGroup($statistics, $vehicleGroup);

        $employeeStatisticsDtos = $this->buildStatisticsForManyEmployees($groupStatistics);
        $totalSiteStatisticsDto = $this->calculateTotalSiteStatistics($groupStatistics);

        $groupDto = new SiteGroupPerformanceDto();
        $groupDto->setStatistics($employeeStatisticsDtos);
        $groupDto->setTotal($totalSiteStatisticsDto);

        return $groupDto;
    }

    /**
     * @param TesterPerformanceResult[] $statistics
     * @return MotTestingPerformanceDto
     */
    private function calculateTotalSiteStatistics(array $statistics)
    {
        $totalCount = $this->sumUpTotalTests($statistics);
        $averageGroupTime = $this->calculateTotalAverageTimeForTheWholeSite($statistics, $totalCount);
        $averageVehicleAge = $this->calculateTotalAverageVehicleAgeForTheWholeSite($statistics);
        $isAverageVehicleAgeAvailable = $this->calculateTotalIsAverageAgeAvailable($statistics);
        $failPercentage = $this->calculateSiteFailedTestsPercentage($statistics, $totalCount);

        $totalSiteStatisticsDto = (new MotTestingPerformanceDto())
            ->setAverageTime($averageGroupTime)
            ->setTotal($totalCount)
            ->setAverageVehicleAgeInMonths($averageVehicleAge)
            ->setIsAverageVehicleAgeAvailable($isAverageVehicleAgeAvailable)
            ->setPercentageFailed($failPercentage);

        return $totalSiteStatisticsDto;
    }

    private function sumUpTotalTests(array $statistics)
    {
        return ArrayUtils::aggregate($statistics, 0, function (TesterPerformanceResult $result, $total) {
            return $total + $result->getTotalCount();
        });
    }

    private function calculateSiteFailedTestsPercentage(array $statistics, $totalTestCount)
    {
        $totalFailCount = $this->sumUpFailedTestsInTheSite($statistics);
        return $this->calculateFailedPercentage($totalFailCount, $totalTestCount);
    }

    private function sumUpFailedTestsInTheSite(array $statistics)
    {
        return ArrayUtils::aggregate($statistics, 0, function (TesterPerformanceResult $result, $total) {
            return $total + $result->getFailedCount();
        });
    }

    private function calculateTotalAverageTimeForTheWholeSite(array $statistics, $totalTestCount)
    {
        $totalTime = ArrayUtils::aggregate($statistics, 0, function (TesterPerformanceResult $result, $total) {
            return $total + $result->getTotalTime();
        });

        return new TimeSpan(0, 0, 0, $totalTestCount == 0 ? 0 : floor($totalTime / $totalTestCount));
    }

    /**
     * @param TesterPerformanceResult $result
     * @return EmployeePerformanceDto
     */
    private function buildEmployeePerformanceDto(TesterPerformanceResult $result)
    {
        $dto = new EmployeePerformanceDto();
        /** @var EmployeePerformanceDto $dto */
        $dto = $this->buildMotTestingPerformanceDto($dto, $result);
        $dto->setUsername($result->getUsername());
        $dto->setPersonId($result->getPersonId());

        return $dto;
    }

    /**
     * @param TesterMultiSitePerformanceResult $result
     * @return TesterMultiSitePerformanceDto
     */
    private function buildTesterMultiSitePerformanceDto(TesterMultiSitePerformanceResult $result)
    {
        $dto = new TesterMultiSitePerformanceDto();
        /** @var TesterMultiSitePerformanceDto $dto */
        $dto = $this->buildMotTestingPerformanceDto($dto, $result);
        $dto->setSiteId($result->getSiteId());
        $dto->setSiteName($result->getSiteName());

        $addressMapper = new AddressMapper();
        $dto->setSiteAddress($addressMapper->toDto($result->getSiteAddress()));

        return $dto;
    }

    /**
     * @param TesterMultiSitePerformanceResult[] $statistics
     * @return TesterMultiSitePerformanceReportDto
     */
    public function buildTesterMultiSitePerformanceReportDto(array $statistics)
    {
        $groups = [VehicleClassGroupCode::BIKES, VehicleClassGroupCode::CARS_ETC];

        $dto = new TesterMultiSitePerformanceReportDto();

        $siteDtos = [
            VehicleClassGroupCode::BIKES => [],
            VehicleClassGroupCode::CARS_ETC => [],
        ];

        foreach($groups as $group) {
            $groupStatistics = $this->filterStatisticsByGroup($statistics, $group);
            /** @var TesterMultiSitePerformanceResult $statistic */
            foreach($groupStatistics as $statistic) {
                $siteDtos[$group][]= $this->buildTesterMultiSitePerformanceDto($statistic);
            }
        }

        $dto->setA($siteDtos[VehicleClassGroupCode::BIKES]);
        $dto->setB($siteDtos[VehicleClassGroupCode::CARS_ETC]);

        return $dto;
    }

    private function buildMotTestingPerformanceDto(MotTestingPerformanceDto $dto, AbstractTesterPerformanceResult $statistic)
    {
        $dto->setPercentageFailed($this->calculateFailedPercentage($statistic->getFailedCount(), $statistic->getTotalCount()));
        $dto->setAverageTime($this->getAverageTime($statistic->getTotalTime(), $statistic->getTotalCount()));
        $dto->setAverageVehicleAgeInMonths($statistic->getAverageVehicleAgeInMonths());
        $dto->setIsAverageVehicleAgeAvailable($statistic->getIsAverageVehicleAgeAvailable());
        $dto->setTotal($statistic->getTotalCount());

        return $dto;
    }

    /**
     * @param TesterPerformanceResult[] $statistics
     * @return EmployeePerformanceDto[]
     */
    private function buildStatisticsForManyEmployees(array $statistics)
    {
        $testerStatisticsDtos = [];

        foreach ($statistics as $statistic) {
            $dto = $this->buildEmployeePerformanceDto($statistic);

            $testerStatisticsDtos[] = $dto;
        }

        return $testerStatisticsDtos;
    }

    private function calculateFailedPercentage($failed, $total)
    {
        return $total == 0 ? 0 : $failed / $total * 100;
    }

    private function getAverageTime($cumulativeTestTime, $totalTests)
    {
        $averageSeconds = $totalTests > 0
            ? floor($cumulativeTestTime / $totalTests)
            : 0;

        return new TimeSpan(0, 0, 0, $averageSeconds);
    }

    /**
     * Calculates weighted sum of average vehicle age
     * @param TesterPerformanceResult[] $statistics
     * @return float|int
     */
    private function calculateTotalAverageVehicleAgeForTheWholeSite($statistics)
    {
        $testerCount = count($statistics);

        if ($testerCount < 1) {
            return 0;
        } else {
            $ageSum = ArrayUtils::aggregate($statistics, 0, function (TesterPerformanceResult $result, $total) {
                return $total + $result->getAverageVehicleAgeInMonths() * $result->getTotalCount();
            });

            $totalCount = ArrayUtils::aggregate($statistics, 0, function (TesterPerformanceResult $result, $total) {
                return $result->getIsAverageVehicleAgeAvailable() ? $total + $result->getTotalCount() : $total;
            });

            return ($totalCount !== 0) ? ($ageSum / $totalCount) : 0;
        }
    }

    /**
     * Checks if any tester has vehicle average age available
     * @param TesterPerformanceResult[] $statistics
     * @return bool
     */
    private function calculateTotalIsAverageAgeAvailable($statistics)
    {
        if (count($statistics) < 1) {
            return false;
        } else {
            return ArrayUtils::anyMatch($statistics, function (TesterPerformanceResult $result) {
                return $result->getIsAverageVehicleAgeAvailable();
            });
        }
    }
}
