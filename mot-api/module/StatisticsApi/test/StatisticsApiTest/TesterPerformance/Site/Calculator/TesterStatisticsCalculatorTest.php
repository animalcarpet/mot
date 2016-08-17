<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\Site\Calculator;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Calculator\TesterStatisticsCalculator;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult\TesterPerformanceResult;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\EmployeePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Utility\ArrayUtils;

class TesterStatisticsCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var TesterStatisticsCalculator */
    private $calculator;

    /** @var TesterPerformanceResult[] */
    private $results = [];

    private $user1 = 'tester-1';

    private $user2 = 'tester-2';

    private $groupA = VehicleClassGroupCode::BIKES;

    private $groupB = VehicleClassGroupCode::CARS_ETC;

    public function setUp()
    {
        $this->calculator = new TesterStatisticsCalculator();
    }

    public function testCalculateTotalTestCountForSite()
    {
        // GIVEN one tester did 2 group A tests
        $this->addResult($this->user1, $this->groupA, 2, 0, 0);

        // AND 3 group B tests
        $this->addResult($this->user1, $this->groupB, 3, 0, 0);

        // AND another tester did 4 group A tests
        $this->addResult($this->user2, $this->groupA, 4, 0, 0);

        // AND 10 group B tests
        $this->addResult($this->user2, $this->groupB, 10, 0, 0);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForSite($this->results);

        $this->assertInstanceOf(SitePerformanceDto::class, $stats);

        // THEN the statistics contain correct values
        $this->assertEquals(2, $this->findStats($stats, $this->user1, $this->groupA)->getTotal());
        $this->assertEquals(3, $this->findStats($stats, $this->user1, $this->groupB)->getTotal());
        $this->assertEquals(4, $this->findStats($stats, $this->user2, $this->groupA)->getTotal());
        $this->assertEquals(10, $this->findStats($stats, $this->user2, $this->groupB)->getTotal());

        // AND total site statistics are correct
        $this->assertEquals(2 + 4, $stats->getA()->getTotal()->getTotal());
        $this->assertEquals(3 + 10, $stats->getB()->getTotal()->getTotal());
    }

    public function testCalculateTotalTestCountForTester()
    {
        // GIVEN one tester did 2 group A tests
        $this->addResult($this->user1, $this->groupA, 2, 0, 0);

        // AND 3 group B tests
        $this->addResult($this->user1, $this->groupB, 3, 0, 0);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForTester($this->results);

        $this->assertInstanceOf(TesterPerformanceDto::class, $stats);

        // THEN the statistics contain correct values
        $this->assertEquals(2, $stats->getGroupAPerformance()->getTotal());
        $this->assertEquals(3, $stats->getGroupBPerformance()->getTotal());
    }

    public function testCalculateFailurePercentageForSite()
    {
        // GIVEN one tester did 2 group A tests and failed 0
        $this->addResult($this->user1, $this->groupA, 2, 0, 0);

        // AND 5 group B tests and failed 1
        $this->addResult($this->user1, $this->groupB, 5, 1, 0);

        // AND another tester did 6 group A tests and failed 6
        $this->addResult($this->user2, $this->groupA, 6, 6, 0);

        // AND 0 group B
        $this->addResult($this->user2, $this->groupB, 0, 0, 0);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForSite($this->results);

        // THEN the statistics contain correct failed percentage
        $this->assertEquals(0, $this->findStats($stats, $this->user1, $this->groupA)->getPercentageFailed());
        $this->assertEquals(20, $this->findStats($stats, $this->user1, $this->groupB)->getPercentageFailed());
        $this->assertEquals(100, $this->findStats($stats, $this->user2, $this->groupA)->getPercentageFailed());
        $this->assertEquals(0, $this->findStats($stats, $this->user2, $this->groupB)->getPercentageFailed());

        // AND whole site statistics are correct
        $expectedAPercentage = 75; // = 6/(2+6)
        $this->assertEquals($expectedAPercentage, $stats->getA()->getTotal()->getPercentageFailed());
        $expectedBPercentage = 20; // = 1/5
        $this->assertEquals($expectedBPercentage, $stats->getB()->getTotal()->getPercentageFailed());
    }

    public function testCalculateFailurePercentageForTester()
    {
        // GIVEN one tester did 2 group A tests and failed 0
        $this->addResult($this->user1, $this->groupA, 2, 0, 0);

        // AND 5 group B tests and failed 1
        $this->addResult($this->user1, $this->groupB, 5, 1, 0);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForTester($this->results);

        // THEN the statistics contain correct failed percentage
        $this->assertEquals(0, $stats->getGroupAPerformance()->getPercentageFailed());
        $this->assertEquals(20, $stats->getGroupBPerformance()->getPercentageFailed());
    }

    /**
     * @dataProvider dataProviderTestCalculateAverageVehicleAge
     * @param $tester1testCount
     * @param $tester2testCount
     * @param $tester1AverageVehicleAge
     * @param $tester2AverageVehicleAge
     * @param $tester1IsAverageAvailable
     * @param $tester2IsAverageAvailable
     * @param $expectedSiteAverageAge
     * @param $expectedSiteHasVehicleAgeAvailable
     */
    public function testCalculateAverageVehicleAgeForSite(
        $tester1testCount, $tester2testCount, $tester1AverageVehicleAge, $tester2AverageVehicleAge,
        $tester1IsAverageAvailable, $tester2IsAverageAvailable,
        $expectedSiteAverageAge, $expectedSiteHasVehicleAgeAvailable
    )
    {
        // GIVEN one tester did tests in VTS
        $this->addResult($this->user1, $this->groupA, $tester1testCount, 0, 0, $tester1AverageVehicleAge, $tester1IsAverageAvailable);

        // AND another tester did tests in VTS
        $this->addResult($this->user2, $this->groupA, $tester2testCount, 0, 0, $tester2AverageVehicleAge, $tester2IsAverageAvailable);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForSite($this->results);

        // THEN the statistics contain correct average vehicle age and flag about having that age
        $this->assertEquals($expectedSiteAverageAge, $stats->getA()->getTotal()->getAverageVehicleAgeInMonths());
        $this->assertEquals($expectedSiteHasVehicleAgeAvailable, $stats->getA()->getTotal()->getIsAverageVehicleAgeAvailable());
    }

    /**
     * @dataProvider dataProviderTestCalculateAverageVehicleAgeForTester
     * @param $tester1testCount
     * @param $tester1AverageVehicleAge
     * @param $tester1IsAverageAvailable
     * @param $expectedAverageAge
     * @param $expectedHasVehicleAgeAvailable
     */
    public function testCalculateAverageVehicleAgeForTester(
        $tester1testCount,
        $tester1AverageVehicleAge,
        $tester1IsAverageAvailable,
        $expectedAverageAge,
        $expectedHasVehicleAgeAvailable
    )
    {
        // GIVEN one tester did tests in VTS
        $this->addResult($this->user1, $this->groupA, $tester1testCount, 0, 0, $tester1AverageVehicleAge, $tester1IsAverageAvailable);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForTester($this->results);

        // THEN the statistics contain correct average vehicle age and flag about having that age
        $this->assertEquals($expectedAverageAge, $stats->getGroupAPerformance()->getAverageVehicleAgeInMonths());
        $this->assertEquals($expectedHasVehicleAgeAvailable, $stats->getGroupAPerformance()->getIsAverageVehicleAgeAvailable());
        $this->assertNull($stats->getGroupBPerformance());
    }

    public function testCalculateAverageTestTimeForSite()
    {
        // GIVEN one tester did 2 group A tests in total time 2:00
        $this->addResult($this->user1, $this->groupA, 2, 0, 120);

        // AND 5 group B tests in 20 days
        $this->addResult($this->user1, $this->groupB, 5, 0, 20 * 24 * 60 * 60);

        // AND another tester did 6 group A in one hour
        $this->addResult($this->user2, $this->groupA, 6, 0, 3600);

        // AND 0 group B tests in zero time
        $this->addResult($this->user2, $this->groupB, 0, 0, 0);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForSite($this->results);

        // THEN the statistics contain correct average time
        $this->assertEquals(new TimeSpan(0, 0, 1, 0), $this->findStats($stats, $this->user1, $this->groupA)->getAverageTime());
        $this->assertEquals(new TimeSpan(4, 0, 0, 0), $this->findStats($stats, $this->user1, $this->groupB)->getAverageTime());
        $this->assertEquals(new TimeSpan(0, 0, 10, 0), $this->findStats($stats, $this->user2, $this->groupA)->getAverageTime());
        $this->assertEquals(new TimeSpan(0, 0, 0, 0), $this->findStats($stats, $this->user2, $this->groupB)->getAverageTime());

        $this->assertEquals(new TimeSpan(0, 0, 7, 45), $stats->getA()->getTotal()->getAverageTime());
        $this->assertEquals(new TimeSpan(4, 0, 0, 0), $stats->getB()->getTotal()->getAverageTime());
    }

    public function testCalculateAverageTestTimeForTester()
    {
        // GIVEN one tester did 2 group A tests in total time 2:00
        $this->addResult($this->user1, $this->groupA, 2, 0, 120);

        // AND 5 group B tests in 20 days
        $this->addResult($this->user1, $this->groupB, 5, 0, 20 * 24 * 60 * 60);

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForTester($this->results);

        // THEN the statistics contain correct average time
        $this->assertEquals(new TimeSpan(0, 0, 1, 0), $stats->getGroupAPerformance()->getAverageTime());
        $this->assertEquals(new TimeSpan(4, 0, 0, 0), $stats->getGroupBPerformance()->getAverageTime());
    }

    public function testTotalSiteStatisticsHandleDivisionByZero()
    {
        // GIVEN no tests where done in a site

        // WHEN I get statistics
        $stats = $this->calculator->calculateStatisticsForSite($this->results);

        // THEN the average time is 0
        $this->assertEquals(new TimeSpan(0, 0, 0, 0), $stats->getA()->getTotal()->getAverageTime());
        $this->assertEquals(new TimeSpan(0, 0, 0, 0), $stats->getB()->getTotal()->getAverageTime());

        // THEN the average test failure rate is 0
        $this->assertEquals(0, $stats->getA()->getTotal()->getPercentageFailed());
        $this->assertEquals(0, $stats->getB()->getTotal()->getPercentageFailed());

        // THEN the total number of tests is 0
        $this->assertEquals(0, $stats->getA()->getTotal()->getTotal());
        $this->assertEquals(0, $stats->getB()->getTotal()->getTotal());
    }

    /**
     * @param SitePerformanceDto $statistics
     * @param $username
     * @param $group
     * @return EmployeePerformanceDto
     * @throws \Exception
     */
    private function findStats(SitePerformanceDto $statistics, $username, $group)
    {
        $groupStatistics = $group == VehicleClassGroupCode::BIKES
            ? $statistics->getA()->getStatistics()
            : $statistics->getB()->getStatistics();

        $filtered = ArrayUtils::filter($groupStatistics, function (EmployeePerformanceDto $userStat) use ($username) {
            return $userStat->getUsername() == $username;
        });

        $hitCount = count($filtered);

        if ($hitCount >= 2) {
            throw new \Exception("There should only be one result per person per group");
        }

        return ArrayUtils::firstOrNull($filtered);
    }

    private function addResult(
        $username, $groupA, $totalCount, $failedCount, $totalTestTime, $averageVehicleAge = null, $isAverageAgeAvailable = false
    )
    {
        $this->results[] = (new TesterPerformanceResult())
            ->setUsername($username)
            ->setTotalCount($totalCount)
            ->setVehicleClassGroup($groupA)
            ->setFailedCount($failedCount)
            ->setAverageVehicleAgeInMonths($averageVehicleAge)
            ->setIsAverageVehicleAgeAvailable($isAverageAgeAvailable)
            ->setTotalTime($totalTestTime);
    }

    public function dataProviderTestCalculateAverageVehicleAge()
    {
        /**
         * Arguments:
         * $tester1testCount,
         * $tester2testCount,
         * $tester1AverageAge,
         * $tester2AverageAge,
         * $tester1IsAverageAvailable,
         * $tester2IsAverageAvailable,
         * $expectedSiteAverageAge,
         * $expectedSiteHasVehicleAgeAvailable
         */
        return [
            // every user has done some tests
            [2, 1, 12, 16, true, true, (2 * 12 + 1 * 16) / (2 + 1), true],
            [10, 12, 157, 139, true, true, (10 * 157 + 12 * 139) / (10 + 12), true],
            // only one user has done tests
            [2, 0, 12, 0, true, false, (2 * 12) / 2, true],
            [0, 5, 0, 45, false, true, (5 * 45) / 5, true],
            // both users did some tests but all vehicles of second user have manufacture_date missing
            [3, 4, 12, 0, true, false, 3 * 12 / 3, true],
            [9, 7, 0, 37, false, true, 7 * 37 / 7, true],
            // both users did tests on vehicles without manufacture_date
            [4, 8, 0, 0, false, false, 0, false],
            [15, 2, 0, 0, false, false, 0, false],
            // no tests were done
            [0, 0, 0, 0, false, false, 0, false],
        ];
    }

    public function dataProviderTestCalculateAverageVehicleAgeForTester()
    {
        /**
         * Arguments:
         * $tester1testCount,
         * $tester1AverageAge,
         * $tester1IsAverageAvailable,
         * $expectedAverageAge,
         * $expectedHasVehicleAgeAvailable
         */
        return [
            // user has done some tests
            [2, 12, true, 12, true],
            [10, 157, true, 157, true],
            // user did tests on vehicles without manufacture_date
            [4, 0, false, 0, false],
            // no tests were done
            [0, 0, false, 0, false],
        ];
    }


}
