<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Generator\ComponentBreakdownMotTestGenerator;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\Statistics\ComponentBreakdownCalculator;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\Dto\Site\SiteDto;
use PHPUnit_Framework_Assert as PHPUnit;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;

class StatsComponentFailRateContext implements Context
{
    /** @var TestSupportHelper */
    private $testSupportHelper;

    private $userData;

    private $motTestData;

    private $vehicleData;

    private $apiResourceHelper;

    public function __construct(
        TestSupportHelper $testSupportHelper,
        UserData $userData,
        MotTestData $motTestData,
        VehicleData $vehicleData,
        ApiResourceHelper $apiResourceHelper
    )
    {
        $this->testSupportHelper = $testSupportHelper;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->vehicleData = $vehicleData;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /** @BeforeScenario @test-quality-information */
    public function clearAmazonCache(BeforeScenarioScope $scope)
    {
        $this->testSupportHelper->getStatisticsAmazonCacheService()->removeAll();
    }

    /**
     * @Given there are tests with reason for rejection performed at site :site by :tester
     */
    public function thereAreTestsWithReasonForRejectionPerformedAtSiteBy(SiteDto $site, AuthenticatedUser $tester)
    {
        $motTestGenerator = new ComponentBreakdownMotTestGenerator($this->motTestData, $this->vehicleData);
        $motTestGenerator->generate($site, $tester);
    }

    /**
     * @Then I should be able to see component fail rate statistics performed :months months ago at site :site for tester :tester and group :group
     */
    public function iShouldBeAbleToSeeComponentFailRateStatisticsPerformedMonthsAgoAtSiteForTesterAndGroup($months, SiteDto $site, AuthenticatedUser $tester, $group)
    {
        $date = new \DateTime(sprintf("first day of %s months ago", $months));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        $calculator = new ComponentBreakdownCalculator($this->motTestData->getAll());
        $expectedComponentBreakdown = $calculator->calculateStatisticsForSite($site->getId(), $tester->getUserId(), $months, $group);

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());
        $this->assertComponents($expectedComponentBreakdown->getComponents(), $componentBreakdown->getComponents());
        PHPUnit::assertEquals($expectedComponentBreakdown->getGroupPerformance(), $componentBreakdown->getGroupPerformance());
    }

    /**
     * @param ComponentDto[] $expectedComponents
     * @param ComponentDto[] $actualComponents
     */
    private function assertComponents(array $expectedComponents, array $actualComponents)
    {
        $findComponentById = function ($categoryId) use ($actualComponents) {
            $component = null;
            foreach ($actualComponents as $actualComponent) {
                if ($categoryId === $actualComponent->getId()) {
                    $component = $actualComponent;
                }
            }

            if ($component === null) {
                throw new \InvalidArgumentException(sprintf("Component with id '%d' not found", $categoryId));
            }

            return $component;
        };

        foreach ($expectedComponents as $expectedComponent) {
            /** @var ComponentDto $actualComponent */
            $actualComponent = $findComponentById($expectedComponent->getId());
            PHPUnit::assertEquals($expectedComponent->getPercentageFailed(), $actualComponent->getPercentageFailed());
        }
    }

    /**
     * @Then there is no component fail rate statistics performed :months months ago at site :site for tester :tester and group :group
     */
    public function thereIsNoComponentFailRateStatisticsPerformedMonthsAgoAtSiteForTesterAndGroup($months, SiteDto $site, AuthenticatedUser $tester, $group)
    {
        $date = new \DateTime(sprintf("first day of %s months ago", $months));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());
        $this->assertEmptyComponentBreakdown($componentBreakdown);

    }

    private function assertEmptyComponentBreakdown(ComponentBreakdownDto $componentBreakdown)
    {
        foreach ($componentBreakdown->getComponents() as $component) {
            PHPUnit::assertEquals(0, $component->getPercentageFailed());
        }

        PHPUnit::assertEquals(0, $componentBreakdown->getGroupPerformance()->getTotal());

        $averageTime = $componentBreakdown->getGroupPerformance()->getAverageTime();

        PHPUnit::assertEquals(0, $averageTime->getHours());
        PHPUnit::assertEquals(0, $averageTime->getDays());
        PHPUnit::assertEquals(0, $averageTime->getMinutes());
        PHPUnit::assertEquals(0, $averageTime->getSeconds());
        PHPUnit::assertEquals(0, $componentBreakdown->getGroupPerformance()->getAverageVehicleAgeInMonths());
        PHPUnit::assertFalse($componentBreakdown->getGroupPerformance()->getIsAverageVehicleAgeAvailable());
    }

    /**
     * @Then I should be able to see national fail rate statistics performed :months months ago for group :group
     */
    public function iShouldBeAbleToSeeNationalFailRateStatisticsPerformedMonthsAgoForTesterAndGroup($months, $group)
    {
        $date = new \DateTime(sprintf("first day of %s months ago", $months));

        $month = (int)$date->format("m");
        $year = (int)$date->format("Y");

        /** @var NationalComponentStatisticApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(NationalComponentStatisticApiResource::class);
        $nationalComponentStatistics = $apiResource->getForDate($group, $month, $year);

        PHPUnit::assertEquals($group, $nationalComponentStatistics->getGroup());
        PHPUnit::assertEquals($month, $nationalComponentStatistics->getMonth());
        PHPUnit::assertEquals($year, $nationalComponentStatistics->getYear());
    }

    /**
     * @Then I should be able to see component fail rate statistics performed :months months ago for tester :tester and group :group
     */
    public function iShouldBeAbleToSeeComponentFailRateStatisticsPerformedMonthsAgoForTesterAndGroup($months, AuthenticatedUser $tester, $group)
    {
        $date = new \DateTime(sprintf("first day of %s months ago", $months));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTester($tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        $calculator = new ComponentBreakdownCalculator($this->motTestData->getAll());
        $expectedComponentBreakdown = $calculator->calculateStatisticsForAllSites($tester->getUserId(), $months, $group);

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());
        $this->assertComponents($expectedComponentBreakdown->getComponents(), $componentBreakdown->getComponents());
        PHPUnit::assertEquals($expectedComponentBreakdown->getGroupPerformance(), $componentBreakdown->getGroupPerformance());
    }

    /**
     * @Then there is no component fail rate statistics performed :months months ago for tester :tester and group :group
     */
    public function thereIsNoComponentFailRateStatisticsPerformedMonthsAgoForTesterAndGroup($months, AuthenticatedUser $tester, $group)
    {
        $date = new \DateTime(sprintf("first day of %s months ago", $months));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTester($tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());
        $this->assertEmptyComponentBreakdown($componentBreakdown);
    }
}

