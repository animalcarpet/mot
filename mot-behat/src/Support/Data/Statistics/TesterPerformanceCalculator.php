<?php
namespace Dvsa\Mot\Behat\Support\Data\Statistics;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Person\PersonDto;

class TesterPerformanceCalculator
{
    private $filter;
    private $motCollection;

    public function __construct(DataCollection $motCollection)
    {
        if ($motCollection->getExpectedInstance() !== MotTestDto::class) {
            throw new \InvalidArgumentException(sprintf("Expected collection of '%s', got '%s", MotTestDto::class, $motCollection->getExpectedInstance()));
        }

        $this->motCollection = $motCollection;
        $this->filter = new MotTestFilter();
    }

    public function calculateTesterPerformanceStatisticsForSite($siteId, $months)
    {
        $motTests = $this->filter->filterByMonths($this->motCollection, $months);
        $motTests = $this->filter->filterBySiteId($motTests, $siteId);

        $groupAmotTests = $this->filter->filterTestsForGroupA($motTests);
        $groupBmotTests = $this->filter->filterTestsForGroupB($motTests);

        $groupAStatistics = $this->calculateGroupStatistics($groupAmotTests);
        $groupBStatistics = $this->calculateGroupStatistics($groupBmotTests);

        $groupAStatistics = $this->addTestersWithoutTestsInThisGroup($groupAStatistics, $motTests);
        $groupBStatistics = $this->addTestersWithoutTestsInThisGroup($groupBStatistics, $motTests);

        $sitePerformance = new SitePerformanceDto();
        $sitePerformance
            ->setA($this->mapToSitePerformanceStatistics($groupAmotTests, $groupAStatistics))
            ->setB($this->mapToSitePerformanceStatistics($groupBmotTests, $groupBStatistics));

        return $sitePerformance;
    }

    public function calculateTesterPerformanceStatisticsForTester($testerId, $months)
    {
        $motTests = $this->filter->filterByMonths($this->motCollection, $months);

        $groupAmotTests = $this->filter->filterTestsForGroupA($motTests);
        $groupBmotTests = $this->filter->filterTestsForGroupB($motTests);

        $testers = $this->getTestersId($motTests);

        $username = $testers[$testerId];

        $groupAemployeePerformance = $this->calculateEmployeePerformance($groupAmotTests, $testerId, $username);
        $groupBemployeePerformance = $this->calculateEmployeePerformance($groupBmotTests, $testerId, $username);

        $groupAemployeePerformance = $this->addTestersWithoutTestsInThisGroup($groupAemployeePerformance, $motTests);
        $groupBemployeePerformance = $this->addTestersWithoutTestsInThisGroup($groupBemployeePerformance, $motTests);

        $testerPerformance = new TesterPerformanceDto();
        $testerPerformance
            ->setGroupAPerformance($groupAemployeePerformance)
            ->setGroupBPerformance($groupBemployeePerformance);


        return $testerPerformance;
    }

    public function calculateNationalTesterPerformanceStatisticsForPrevMonth(DataCollection $motCollection, $months)
    {
        $motTests = $this->filter->filterByMonths($this->motCollection, $months);

        $groupAmotTests = $this->filter->filterTestsForGroupA($motTests);
        $groupBmotTests = $this->filter->filterTestsForGroupB($motTests);

        $date = new \DateTime(sprintf("first day of %d months ago", $months));
        return [
            "month" => (int)$date->format("m"),
            "year" => (int)$date->format("Y"),
            "groupA" => $this->calculateStats($groupAmotTests),
            "groupB" => $this->calculateStats($groupBmotTests),
        ];
    }

    private function calculateGroupStatistics(DataCollection $motCollection)
    {
        $groupStatistics = [];
        $testers = $this->getTestersId($motCollection);

        /** @var PersonDto $tester */
        foreach ($testers as $tester) {
            $employeePerformance = $this->calculateEmployeePerformance($motCollection, $tester->getId(), $tester);

            if ($employeePerformance !== null) {
                $groupStatistics[] = $employeePerformance;
            }
        }

        return $groupStatistics;
    }

    private function addTestersWithoutTestsInThisGroup($groupPerformance, DataCollection $motTestCollection)
    {
        /** @var PersonDto[] $testers */
        $testers = $this->getTestersId($motTestCollection);

        if (empty($groupPerformance)) {
            $groupPerformance = [];
            foreach ($testers as $tester) {
                $employee = $this->getEmployeeWithDefaultValues($tester);
                $groupPerformance[] = $employee;
            }
        }

        return $groupPerformance;
    }

    private function getEmployeeWithDefaultValues(PersonDto $tester) {
        $employee = new EmployeePerformanceDto();
        $employee->setAverageVehicleAgeInMonths(0)
            ->setIsAverageVehicleAgeAvailable(false)
            ->setPercentageFailed(0)
            ->setTotal(0)
            ->setAverageTime(new TimeSpan(0, 0, 0, 0))
            ->setUsername($tester->getUsername())
            ->setFirstName($tester->getFirstName())
            ->setFamilyName($tester->getFamilyName())
            ->setMiddleName($tester->getMiddleName())
            ->setPersonId($tester->getId());

        return $employee;
    }

    private function calculateEmployeePerformance(DataCollection $motCollection, $testerId, PersonDto $tester)
    {
        $tests = $this->filter->filterByTesterId($motCollection, $testerId);
        if (count($tests) > 0) {
            $employeePerformance = $this->calculateStats($tests);
            $employeePerformance
                ->setUsername($tester->getUsername())
                ->setFirstName($tester->getFirstName())
                ->setFamilyName($tester->getFamilyName())
                ->setMiddleName($tester->getMiddleName())
                ->setPersonId($testerId);
            return $employeePerformance;
        } else {
            return $this->getEmployeeWithDefaultValues($tester);
        }
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

    private function calculateStats($motCollection)
    {
        $stats = new PerformanceCalculator($motCollection);

        $employeePerformance = new EmployeePerformanceDto();
        $employeePerformance
            ->setAverageVehicleAgeInMonths($stats->getAverageVehicleAgeInMonths())
            ->setIsAverageVehicleAgeAvailable($stats->getIsAverageVehicleAgeAvailable())
            ->setTotal($stats->getTotal())
            ->setPercentageFailed($stats->getPercentageFailed())
            ->setAverageTime($stats->getAverageTime());

        return $employeePerformance;
    }

    private function getTestersId(DataCollection $motCollection)
    {
        $testers = [];
        /** @var MotTestDto $mot */
        foreach ($motCollection as $mot) {
            if (!in_array($mot->getTester(), $testers)) {
                $testers[$mot->getTester()->getId()] = $mot->getTester();
            }
        }

        return $testers;
    }
}
