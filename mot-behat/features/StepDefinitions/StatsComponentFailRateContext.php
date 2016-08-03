<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\StatsComponentFailRate;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use PHPUnit_Framework_Assert as PHPUnit;

class StatsComponentFailRateContext implements Context
{
    /** @var TestSupportHelper  */
    private $testSupportHelper;

    /** @var Session */
    private $session;

    /** @var StatsComponentFailRate  */
    private $statsComponentFailRate;

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
        StatsComponentFailRate $statsComponentFailRate
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->session = $session;
        $this->statsComponentFailRate = $statsComponentFailRate;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
    }

    /** @BeforeScenario @test-quality-information */
    public function clearAmazonCache(BeforeScenarioScope $scope)
    {
        $this->testSupportHelper->getStatisticsAmazonCacheService()->removeAll();
        $this->testSupportHelper->getMotService()->removeAllTests();
    }

    /**
     * @Then being log in as a :userName I can view component fail rate statistics for tester :testerName and group :group and site :siteName with no data
     */
    public function beingLogInAsAICanViewComponentFailRateStatisticsForTesterAndGroupAndSiteWithNoData($userName, $testerName, $group, $siteName)
    {
        $user = $this->personContext->getUser($userName);
        $tester = $this->personContext->getUser($testerName);
        $site = $this->vtsContext->getSite($siteName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));

        $response = $this->statsComponentFailRate->getTesterComponentFailRate(
            $user->getAccessToken(),
            $site["id"],
            $tester->getUserId(),
            $group,
            $date->format("Y"),
            $date->format("m")
        );

        $stats = $response->getBody()->toArray()["data"];
        PHPUnit::assertGreaterThan(0, $stats["components"]);
        foreach ($stats["components"] as $component) {
            PHPUnit::assertEquals(0, $component["percentageFailed"]);
        }
    }

    /**
     * @Then being log in as a :userName I can view component group performance statistics for tester :testerName and group :group and site :siteName with data:
     */
    public function beingLogInAsAICanViewComponentGroupPerformanceStatisticsForTesterAndGroupAndSiteWithData($userName, $testerName, $group, $siteName, TableNode $table)
    {
        $user = $this->personContext->getUser($userName);
        $tester = $this->personContext->getUser($testerName);
        $site = $this->vtsContext->getSite($siteName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));

        $response = $this->statsComponentFailRate->getTesterComponentFailRate(
            $user->getAccessToken(),
            $site["id"],
            $tester->getUserId(),
            $group,
            $date->format("Y"),
            $date->format("m")
        );

        $stats = $response->getBody()->toArray()["data"];
        $groupPerformance = $stats["groupPerformance"];

        $rows = $table->getColumnsHash();
        $expectedData = $rows[0];

        PHPUnit::assertEquals($expectedData["total"], $groupPerformance["total"]);
        PHPUnit::assertEquals($expectedData["averageTime"], $groupPerformance["averageTime"]);
        PHPUnit::assertEquals($expectedData["percentageFailed"], $groupPerformance["percentageFailed"]);
        PHPUnit::assertEquals($expectedData["averageVehicleAgeInMonths"], $groupPerformance["averageVehicleAgeInMonths"]);

        $isAverageVehicleAgeAvailable = $expectedData["isAverageVehicleAgeAvailable"] === "true";

        PHPUnit::assertEquals($isAverageVehicleAgeAvailable, $groupPerformance["isAverageVehicleAgeAvailable"]);
    }

    /**
     * @Then being log in as a :userName I can view component fail rate statistics for tester :testerName and group :group and site :siteName with data:
     */
    public function beingLogInAsAICanViewComponentFailRateStatisticsForTesterAndGroupAndSiteWithData($userName, $testerName, $group, $siteName, TableNode $table)
    {
        $user = $this->personContext->getUser($userName);
        $tester = $this->personContext->getUser($testerName);
        $site = $this->vtsContext->getSite($siteName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));

        $response = $this->statsComponentFailRate->getTesterComponentFailRate(
            $user->getAccessToken(),
            $site["id"],
            $tester->getUserId(),
            $group,
            $date->format("Y"),
            $date->format("m")
        );

        $stats = $response->getBody()->toArray()["data"];
        $components = $stats["components"];

        PHPUnit::assertGreaterThan(0, $components);

        $expectedComponents= $table->getColumnsHash();
        foreach ($expectedComponents as $expectedComponent) {
            $this->assertComponent($expectedComponent, $components);
        }
    }

    private function assertComponent(array $expectedComponent, array $components)
    {
        foreach ($components as $component) {
            if ($component["name"] === $expectedComponent["componentName"]) {
                PHPUnit::assertEquals($expectedComponent["percentageFailed"], $component["percentageFailed"]);
            } else {
                PHPUnit::assertEquals(0, $component["percentageFailed"]);
            }
        }
    }

    /**
     * @Then being log in as a :userName I can view national fail rate statistics for group :group with no data
     */
    public function beingLogInAsAICanViewNationalFailRateStatisticsForGroupWithNoData($userName, $group)
    {
        $user = $this->personContext->getUser($userName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));
        $response = $this
            ->statsComponentFailRate
            ->getNationalComponentFailRate($user->getAccessToken(), $group, $date->format("Y"), $date->format("m"));

        $stats = $response->getBody()->toArray()["data"];
        PHPUnit::assertGreaterThan(0, $stats["components"]);

        foreach ($stats["components"] as $component) {
            PHPUnit::assertEquals(0, $component["percentageFailed"]);
        }
    }

    /**
     * @Then being log in as a :userName I can view national fail rate statistics for group :group with data:
     */
    public function beingLogInAsAICanViewNationalFailRateStatisticsForSiteWithData($userName, $group, TableNode $table)
    {
        $user = $this->personContext->getUser($userName);

        $date = (new \DateTime())->sub(new \DateInterval("P1M"));

        $response = $this->statsComponentFailRate->getNationalComponentFailRate($user->getAccessToken(), $group, $date->format("Y"), $date->format("m"));

        $stats = $response->getBody()->toArray()["data"];
        $components = $stats["components"];

        $expectedComponents = $table->getColumnsHash();

        PHPUnit::assertGreaterThan(0, $components);

        foreach ($expectedComponents as $expectedComponent) {
            $this->assertComponent($expectedComponent, $components);
        }
    }

}
