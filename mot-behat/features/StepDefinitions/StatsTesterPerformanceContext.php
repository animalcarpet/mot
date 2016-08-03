<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Helper\RfrHelper;
use Dvsa\Mot\Behat\Support\Api\StatsTesterPerformance;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Enum\VehicleClassGroupCode;
use PHPUnit_Framework_Assert as PHPUnit;

class StatsTesterPerformanceContext implements Context
{
    /**
     * @var AuthorisedExaminerContext
     */
    protected $aeContext;
    /** @var TestSupportHelper  */
    private $testSupportHelper;

    /** @var Session */
    private $session;

    /** @var StatsTesterPerformance  */
    private $statsTesterPerformance;

    /** @var SessionContext */
    private $sessionContext;

    /** @var VehicleContext */
    private $vehicleContext;

    /** @var  MotTestContext */
    private $motTestContext;

    /** @var VtsContext */
    private $vtsContext;

    /** @var PersonContext */
    private $personContext;

    /**
     * @param TestSupportHelper $testSupportHelper
     */
    public function __construct(
        TestSupportHelper $testSupportHelper,
        Session $session,
        StatsTesterPerformance $statsTesterPerformance
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->session = $session;
        $this->statsTesterPerformance = $statsTesterPerformance;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vehicleContext = $scope->getEnvironment()->getContext(VehicleContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
        $this->aeContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
    }

    /** @BeforeScenario @test-quality-information */
    public function clearAmazonCache(BeforeScenarioScope $scope)
    {
        $this->testSupportHelper->getStatisticsAmazonCacheService()->removeAll();
        $this->testSupportHelper->getMotService()->removeAllTests();
    }

    private function thereIsATestCreatedForVehicleWithGroup(array $data)
    {
        $status = $data['status'];
        $testType = $data['type'];
        $username = $data['testername'];
        $date = array_key_exists('started_date', $data)? $data['started_date'] : "now";
        $vehicleClass = $data['vehicle_class'];
        $duration = array_key_exists('duration', $data)? $data['duration'] : 60;
        $siteName = $data['site'];
        $rfrName = array_key_exists('rfr', $data)? $data['rfr']  : null;

        if (array_key_exists('date_of_manufacture', $data)) {
            $dateOfManufacture = (new \DateTime($data['date_of_manufacture']))->format("Y/m/d");
        } else {
            $dateOfManufacture = '1980/01/01';
        }

        if ($vehicleClass > 2) {
            $rfrId = RfrHelper::getRfrForClass3AndAboveByName($rfrName);
        } else {
            $rfrId = RfrHelper::getRfrForClass1And2ByName($rfrName);
        }

        $siteId = $this->vtsContext->getSite($siteName)["id"];

        $startDate = new \DateTime($date);
        $completedDate = clone $startDate;
        $completedDate->add(new \DateInterval('PT' . $duration . 'M'));

        $token = $this->personContext->getUser($username)->getAccessToken();
        $vehicleId = $this->vehicleContext->createVehicle(["testClass" => $vehicleClass, "dateOfManufacture" => $dateOfManufacture]);

        $params = [
            "vehicleId" => $vehicleId,
            "vehicleClass" => $vehicleClass,
            "siteId" => $siteId,
            "token" => $token,
            "rfrId" => $rfrId
        ];

        $this->motTestContext->createCompletedMotTest($status, $testType, $params);
        $motTestNumber = $this->motTestContext->getRawMotTestData()->getBody()->toArray();
        $motTestNumber = $motTestNumber["data"]["motTestNumber"];
        $this->testSupportHelper->getMotService()->changeDate($motTestNumber, $startDate, $completedDate);
    }

    /**
     * @Given there is a test created for vehicle with the following data:
     */
    public function thereIsATestCreatedForVehicleWithTheFollowingData(TableNode $table)
    {
        $columns = $table->getColumnsHash();
        foreach ($columns as $row) {
            $this->thereIsATestCreatedForVehicleWithGroup($row);
        }
    }

    /**
     * @Then being log in as a :username I can view site tester performance statistics for site :siteName with data:
     */
    public function beingLogInAsAICanViewSiteTesterPerformanceStatisticsForSiteWithData($username, $siteName, TableNode $table)
    {
        $user = $this->personContext->getUser($username);
        $site = $this->vtsContext->getSite($siteName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));
        $response = $this
            ->statsTesterPerformance
            ->getSiteTesterPerformance(
                $user->getAccessToken(),
                $site["id"],
                $date->format("Y"),
                $date->format("m")
            )->getBody()->toArray();

        $stats = $response["data"];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            $row["username"] = $this->personContext->getUser($row["testername"])->getUsername();

            if ($row["group"] === VehicleClassGroupCode::BIKES) {
                $this->assertSiteTesterPerformanceForGroupA($stats["a"]["statistics"], $row);
            } elseif ($row["group"] === VehicleClassGroupCode::CARS_ETC) {
                $this->assertSiteTesterPerformanceForGroupB($stats["b"]["statistics"], $row);
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid group '%s'", $row["group"]));
            }
        }
    }

    private function assertSiteTesterPerformanceForGroupA(array $actualData, array $expectedData)
    {
        $this->assertSiteTesterPerformanceStats($actualData, $expectedData, VehicleClassGroupCode::BIKES);
    }

    private function assertSiteTesterPerformanceForGroupB(array $actualData, array $expectedData)
    {
        $this->assertSiteTesterPerformanceStats($actualData, $expectedData, VehicleClassGroupCode::CARS_ETC);
    }

    private function assertSiteTesterPerformanceStats(array $actualData, array $expectedData, $group)
    {
        foreach ($actualData as $data) {
            if ($expectedData["username"] === $data['username'] && $expectedData["group"] === $group) {
                PHPUnit::assertEquals($expectedData["total"], $data["total"]);
                PHPUnit::assertEquals($expectedData["averageTime"], $data["averageTime"]);
                PHPUnit::assertEquals(round($expectedData["percentageFailed"], 2), round($data["percentageFailed"], 2));
                PHPUnit::assertEquals(round($expectedData["averageVehicleAgeInMonths"], 2), round($data["averageVehicleAgeInMonths"], 2));


                $isAverageVehicleAgeAvailable = $expectedData["isAverageVehicleAgeAvailable"] === "true";

                PHPUnit::assertEquals($isAverageVehicleAgeAvailable, $data["isAverageVehicleAgeAvailable"]);
                return;
            }
        }

        $message = "Tester Performance Statistics not found for user '%s' with group '%s'.";
        throw new \Exception(sprintf($message, $actualData['username'], $group));
    }

    /**
     * @Then being log in as a :username I can view total site tester performance statistics for site :siteName with data:
     */
    public function beingLogInAsAICanViewTotalSiteTesterPerformanceStatisticsForSiteWithData($username, $siteName, TableNode $table)
    {
        $user = $this->personContext->getUser($username);
        $site = $this->vtsContext->getSite($siteName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));
        $response = $this
            ->statsTesterPerformance
            ->getSiteTesterPerformance(
                $user->getAccessToken(),
                $site["id"],
                $date->format("Y"),
                $date->format("m")
            )->getBody()->toArray();

        $stats = $response["data"];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            if ($row["group"] === VehicleClassGroupCode::BIKES) {
                $this->assertSiteStats($stats["a"]["total"], $row);
            } elseif ($row["group"] === VehicleClassGroupCode::CARS_ETC) {
                $this->assertSiteStats($stats["b"]["total"], $row);
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid group '%s'", $row["group"]));
            }
        }
    }

    private function assertSiteStats(array $actualData, array $expectedData)
    {
        PHPUnit::assertEquals($expectedData["total"], $actualData["total"]);
        PHPUnit::assertEquals($expectedData["averageTime"], $actualData["averageTime"]);
        PHPUnit::assertEquals(round($expectedData["percentageFailed"], 2), round($actualData["percentageFailed"], 2));
        PHPUnit::assertEquals(round($expectedData["averageVehicleAgeInMonths"], 2),round($actualData["averageVehicleAgeInMonths"]));


        $isAverageVehicleAgeAvailable = $expectedData["isAverageVehicleAgeAvailable"] === "true";

        PHPUnit::assertEquals($isAverageVehicleAgeAvailable, $actualData["isAverageVehicleAgeAvailable"]);
    }

    /**
     * @Then being log in as a :username I can view site national statistics with data:
     */
    public function beingLogInAsAICanViewSiteNationalStatisticsWithData($username, TableNode $table)
    {
        $user = $this->personContext->getUser($username);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));
        $stats = $this
            ->statsTesterPerformance
            ->getNationalTesterPerformance(
                $user->getAccessToken(),
                $date->format("Y"),
                $date->format("m")
                )->getBody()->toArray()["data"];

        $rows = $table->getColumnsHash();
        foreach ($rows as $row) {
            if ($row["group"] === VehicleClassGroupCode::BIKES) {
                $this->assertSiteStats($stats["groupA"], $row);
            } elseif ($row["group"] === VehicleClassGroupCode::CARS_ETC) {
                $this->assertSiteStats($stats["groupB"], $row);
            } else {
                throw new \InvalidArgumentException(sprintf("Invalid group '%s'", $row["group"]));
            }
        }
    }

    /**
     * @Then /^being log in as an aedm in "([^"]*)" I can view authorised examiner statistics with data:$/
     */
    public function beingLogInAsAnAedmInICanViewAuthorisedExaminerStatisticsWithData($aeName, TableNode $table)
    {
        $ae = $this->aeContext->getAe($aeName);
        $userName = $ae["aedm"]["username"];

        $user = $this->session->startSession($userName, "Password1");

        $stats = $this->statsTesterPerformance->getAuthorisedExaminerTesterPerformance($user->getAccessToken(),
            $ae["id"])->getBody()["data"];

        $rows = $table->getRows();
        array_shift($rows);
        $sites = $stats['sites']->toArray();

        PHPUnit::assertEquals(count($sites), $stats['siteTotalCount']);

        foreach($rows as $i => $row) {
            $site = $sites[$i];
            PHPUnit::assertEquals($row[0], $site['name']);
            PHPUnit::assertEquals($row[1], $site['riskAssessmentScore']);
        }

    }


}
