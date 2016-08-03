<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\ParameterCheck\StatisticsParameterCheck;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Repository\SiteStatisticsRepository;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\NotFoundException;

class SiteStatisticsService implements AutoWireableInterface
{
    private $repository;

    private $authorisationService;

    private $dateTimeHolder;

    function __construct(
        SiteStatisticsRepository $repository,
        MotAuthorisationServiceInterface $authorisationService,
        DateTimeHolder $dateTimeHolder
    )
    {
        $this->repository = $repository;
        $this->authorisationService = $authorisationService;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function get($siteId, $year, $month)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);

        $validator = new StatisticsParameterCheck($this->dateTimeHolder);
        if (!$validator->isValid($year, $month)) {
            throw new NotFoundException("Site Statistics");
        }

        $statistics = $this->repository->get($siteId, $year, $month);

        $siteDto = $this->calculateStatistics($statistics);

        return $siteDto;
    }

    /**
     * @param TesterPerformanceResult[] $statistics
     * @return SitePerformanceDto
     */
    private function calculateStatistics(array $statistics)
    {
        $siteDto = new SitePerformanceDto();

        $groupA = $this->createDtoForClassGroup($statistics, VehicleClassGroupCode::BIKES);
        $siteDto->setA($groupA);

        $groupB = $this->createDtoForClassGroup($statistics, VehicleClassGroupCode::CARS_ETC);
        $siteDto->setB($groupB);

        return $siteDto;
    }

    /**
     * @param array $statistics
     * @param $vehicleGroup
     * @return TesterPerformanceResult[]
     */
    private function filterStatisticsByGroup(array $statistics, $vehicleGroup)
    {
        /** @var TesterPerformanceResult[] $groupStatistics */
        $groupStatistics = ArrayUtils::filter($statistics, function (TesterPerformanceResult $statistic) use ($vehicleGroup) {
            return $statistic->getVehicleClassGroup() == $vehicleGroup;
        });

        return $groupStatistics;
    }

    /**
     * @param TesterPerformanceResult[] $statistics
     * @param string $vehicleGroup
     * @return SiteGroupPerformanceDto
     */
    private function createDtoForClassGroup(array $statistics, $vehicleGroup)
    {
        $groupStatistics = $this->filterStatisticsByGroup($statistics, $vehicleGroup);

        $employeeStatisticsDtos = $this->calculateEmployeeStatistics($groupStatistics);
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
     * @param TesterPerformanceResult[] $statistics
     * @return EmployeePerformanceDto[]
     */
    private function calculateEmployeeStatistics(array $statistics)
    {
        $testerStatisticsDtos = [];

        foreach ($statistics as $statistic) {
            $dto = new EmployeePerformanceDto();

            $dto->setPercentageFailed($this->calculateFailedPercentage($statistic->getFailedCount(), $statistic->getTotalCount()));
            $dto->setAverageTime($this->getAverageTime($statistic->getTotalTime(), $statistic->getTotalCount()));
            $dto->setUsername($statistic->getUsername());
            $dto->setPersonId($statistic->getPersonId());
            $dto->setAverageVehicleAgeInMonths($statistic->getAverageVehicleAgeInMonths());
            $dto->setIsAverageVehicleAgeAvailable($statistic->getIsAverageVehicleAgeAvailable());
            $dto->setTotal($statistic->getTotalCount());

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
