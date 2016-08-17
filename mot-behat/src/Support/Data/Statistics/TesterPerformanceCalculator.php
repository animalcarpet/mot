<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class TesterPerformanceCalculator
{
    private $userData;
    private $siteData;

    public function __construct(
        UserData $userData,
        SiteData $siteData
    )
    {
        $this->userData = $userData;
        $this->siteData = $siteData;
    }

    public function calculateTesterPerformanceStatisticsForSite(DataCollection $motCollection, $siteId, $months)
    {
        $motTests = $this->filterTests($motCollection, $months);

        $motTests = $motTests->filter(function (MotTestDto $mot) use ($siteId) {
            return $mot->getVehicleTestingStation()["id"] === $siteId;
        });

        $groupAmotTests = $this->filterTestsForGroupA($motTests);
        $groupBmotTests = $this->filterTestsForGroupB($motTests);

        $testers = $this->getTestersId($motTests);
        $groupAStatistics = [];
        $groupBStatistics = [];
        foreach ($testers as $id => $username) {
            $tests = $this->getTestsByTesterId($id, $groupAmotTests);
            if (count($tests) > 0) {
                $employeePerformance = $this->calculateStats($tests);
                $employeePerformance
                    ->setUsername($username)
                    ->setPersonId($id);
                $groupAStatistics[] = $employeePerformance;
            }

            $tests = $this->getTestsByTesterId($id, $groupBmotTests);
            if (count($tests) > 0) {
                $employeePerformance = $this->calculateStats($tests);
                $employeePerformance
                    ->setUsername($username)
                    ->setPersonId($id);
                $groupBStatistics[] = $employeePerformance;
            }
        }

        $sitePerformance = new SitePerformanceDto();
        $sitePerformance
            ->setA($this->mapToSitePerformanceStatistics($groupAmotTests, $groupAStatistics))
            ->setB($this->mapToSitePerformanceStatistics($groupBmotTests, $groupBStatistics));

        return $sitePerformance;
    }

    public function calculateTesterPerformanceStatisticsForTester(DataCollection $motCollection, $testerId, $months)
    {
        $motTests = $this->filterTests($motCollection, $months);

        $groupAmotTests = $this->filterTestsForGroupA($motTests);
        $groupBmotTests = $this->filterTestsForGroupB($motTests);

        $testers = $this->getTestersId($motTests);

        $id = $testerId;
        $username = $testers[$testerId];

        $groupAemployeePerformance = null;
        $tests = $this->getTestsByTesterId($id, $groupAmotTests);
        if (count($tests) > 0) {
            $groupAemployeePerformance = $this->calculateStats($tests);
            $groupAemployeePerformance
                ->setUsername($username)
                ->setPersonId($id);
        }

        $groupBemployeePerformance = null;
        $tests = $this->getTestsByTesterId($id, $groupBmotTests);
        if (count($tests) > 0) {
            $groupBemployeePerformance = $this->calculateStats($tests);
            $groupBemployeePerformance
                ->setUsername($username)
                ->setPersonId($id);
        }

        $testerPerformance = new TesterPerformanceDto();
        $testerPerformance
            ->setGroupAPerformance($groupAemployeePerformance)
            ->setGroupBPerformance($groupBemployeePerformance);


        return $testerPerformance;
    }

    private function mapToSitePerformanceStatistics($motTests, $groupStatistics)
    {
        $total = $this->calculateStats($motTests);
        $groupATotal = new MotTestingPerformanceDto();
        $groupATotal
            ->setAverageTime($total->getAverageTime())
            ->setAverageVehicleAgeInMonths($total->getAverageVehicleAgeInMonths())
            ->setIsAverageVehicleAgeAvailable($total->getIsAverageVehicleAgeAvailable())
            ->setPercentageFailed($total->getPercentageFailed())
            ->setTotal($total->getTotal());;

        $siteGroupAPerformance = new SiteGroupPerformanceDto();
        $siteGroupAPerformance
            ->setTotal($groupATotal)
            ->setStatistics($groupStatistics);

        return $siteGroupAPerformance;
    }

    public function calculateNationalTesterPerformanceStatisticsForPrevMonth(DataCollection $motCollection, $months)
    {
        $motTests = $this->filterTests($motCollection, $months);
        $groupAMotTests = $this->filterTestsForGroupA($motTests);
        $groupBMotTests = $this->filterTestsForGroupB($motTests);

        $date = new \DateTime(sprintf("first day of %d months ago", $months));
        return [
            "month" => (int)$date->format("m"),
            "year" => (int)$date->format("Y"),
            "groupA" => $this->calculateStats($groupAMotTests),
            "groupB" => $this->calculateStats($groupBMotTests),
        ];
    }

    private function filterTests(DataCollection $motCollection, $months)
    {
        $startDate = new \DateTime(sprintf("first day of %d months ago", $months));
        $startDate->setTime(0, 0, 0);

        $endDate = new \DateTime(sprintf("last day of %d months ago", $months));
        $endDate->setTime(23, 59, 59);

        return $motCollection->filter(function (MotTestDto $mot) use ($startDate, $endDate) {
            $completedDate = new \DateTime($mot->getCompletedDate());
            $emergencyLog = $mot->getEmergencyLog();
            $hasCorrectStatus = in_array($mot->getStatus(), [MotTestStatusName::FAILED, MotTestStatusName::PASSED]);
            $hasCorrectType = ($mot->getTestType()->getCode() === MotTestTypeCode::NORMAL_TEST && empty($emergencyLog));
            return ($completedDate >= $startDate && $completedDate <= $endDate && $hasCorrectStatus && $hasCorrectType);
        });
    }

    private function filterTestsForGroupA(DataCollection $motCollection)
    {
        $vehicleClasses = [VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2];
        return $this->filterTestsWithClass($motCollection, $vehicleClasses);
    }

    private function filterTestsForGroupB(DataCollection $motCollection)
    {
        $vehicleClasses = [VehicleClassCode::CLASS_3, VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_5, VehicleClassCode::CLASS_7];
        return $this->filterTestsWithClass($motCollection, $vehicleClasses);
    }

    private function filterTestsWithClass(DataCollection $motCollection, array $vehicleClasses)
    {
        return $motCollection->filter(function (MotTestDto $mot) use ($vehicleClasses) {
            return in_array($mot->getVehicleClass()->getCode(), $vehicleClasses);
        });
    }

    private function calculateStats($motCollection)
    {
        $employeePerformance = new EmployeePerformanceDto();
        $employeePerformance
            ->setAverageVehicleAgeInMonths(0)
            ->setIsAverageVehicleAgeAvailable(false)
            ->setTotal(0)
            ->setPercentageFailed(0)
            ->setAverageTime(new TimeSpan(0, 0, 0, 0));

        $total = count($motCollection);
        if ($total === 0) {
            return $employeePerformance;
        }

        $time = 0;
        $numOfFailedTests = 0;
        $age = 0;
        /** @var MotTestDto $mot */
        foreach ($motCollection as $mot) {
            $completedDate = new \DateTime($mot->getCompletedDate());
            $startedDate = new \DateTime($mot->getStartedDate());
            $diff = $completedDate->diff($startedDate);

            $time += $diff->d * 24 * 60 + $diff->h * 60 + $diff->i + $diff->s / 60;

            if ($mot->getStatus() === MotTestStatusName::FAILED) {
                $numOfFailedTests++;
            }

            $currentDate = new \DateTime($mot->getCompletedDate());
            $manufactureDate = $mot->getVehicle()->getManufactureDate();
            $diff = $currentDate->diff(new \DateTime($manufactureDate));
            $age += $diff->y * 12 + $diff->m;
        }

        $avgTime = round($time / $total);

        $days = floor($avgTime / (24 * 60));
        $avgTime -= $days * 24 * 60;

        $hours = floor($avgTime / 60);
        $avgTime -= $hours * 60;

        $minutes = floor($avgTime);
        $avgTime -= $minutes;

        $seconds = floor($avgTime * 60);

        $avgVehicleAge = round($age / $total);
        $percentageFailed = ($numOfFailedTests / $total) * 100;

        $employeePerformance
            ->setAverageVehicleAgeInMonths($avgVehicleAge)
            ->setIsAverageVehicleAgeAvailable(true)
            ->setTotal($total)
            ->setPercentageFailed($percentageFailed)
            ->setAverageTime(new TimeSpan($days, $hours, $minutes, $seconds));

        return $employeePerformance;
    }

    private function getTestersId(DataCollection $motCollection)
    {
        $testers = [];
        /** @var MotTestDto $mot */
        foreach ($motCollection as $mot) {
            if (!in_array($mot->getTester()->getId(), $testers)) {
                $testers[$mot->getTester()->getId()] = $mot->getTester()->getUsername();
            }
        }

        return $testers;
    }

    private function getTestsByTesterId($testerId, DataCollection $motCollection)
    {
        return $motCollection->filter(function (MotTestDto $dto) use ($testerId) {
            return $dto->getTester()->getId() === $testerId;
        });
    }
}
