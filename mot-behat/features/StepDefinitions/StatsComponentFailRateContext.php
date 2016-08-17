<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Generator\MotTestGenerator;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\ComponentFailRateApiResource;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\NationalComponentStatisticApiResource;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\VehicleClassCode;
use PHPUnit_Framework_Assert as PHPUnit;

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
        $motTestGenerator = new MotTestGenerator($this->motTestData);

        $dateOfManufacture = new \DateTime("first day of 2 years ago");
        $vehicle = $this->vehicleData->create(["testClass" => VehicleClassCode::CLASS_1, "dateOfManufacture" => $dateOfManufacture->format("Y-m-d")]);
        $motTestGenerator
            ->setDuration(60)
            ->setStartedDate("first day of previous month");
        $motTestGenerator->generatePassedMotTests($tester, $site, $vehicle);

        $dateOfManufacture = new \DateTime("first day of 4 years ago");
        $vehicle = $this->vehicleData->create(["testClass" => VehicleClassCode::CLASS_2, "dateOfManufacture" => $dateOfManufacture->format("Y-m-d")]);
        $motTestGenerator
            ->setDuration(50)
            ->setStartedDate("first day of previous month")
            ->setRfrId(511);
        $motTestGenerator->generateFailedMotTests($tester, $site, $vehicle);

        $dateOfManufacture = new \DateTime("first day of 14 years ago");
        $vehicle = $this->vehicleData->create(["testClass" => VehicleClassCode::CLASS_4, "dateOfManufacture" => $dateOfManufacture->format("Y-m-d")]);
        $motTestGenerator
            ->setDuration(40)
            ->setStartedDate("first day of previous month")
            ->setRfrId(8455);
        $motTestGenerator->generateFailedMotTests($tester, $site, $vehicle);
    }

    /**
     * @Then I should be able to see component fail rate statistics performed :months months ago at site :site for tester :tester and group :group
     */
    public function iShouldBeAbleToSeeComponentFailRateStatisticsPerformedMonthsAgoAtSiteForTesterAndGroup($months, SiteDto $site, AuthenticatedUser $tester, $group)
    {
        $interval = sprintf("P%sM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());
    }

    /**
     * @Then there is no component fail rate statistics performed :months months ago at site :site for tester :tester and group :group
     */
    public function thereIsNoComponentFailRateStatisticsPerformedMonthsAgoAtSiteForTesterAndGroup($months, SiteDto $site, AuthenticatedUser $tester, $group)
    {
        $interval = sprintf("P%dM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $date->format("m"), $date->format("Y"));

        PHPUnit::assertEquals($tester->getUsername(), $componentBreakdown->getUserName());

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
        $interval = sprintf("P%sM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        $month = (int)$date->format("m");
        $year = (int)$date->format("Y");

        /** @var NationalComponentStatisticApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(NationalComponentStatisticApiResource::class);
        $nationalComponentStatistics = $apiResource->getForDate($group, $month, $year);

        PHPUnit::assertEquals($group, $nationalComponentStatistics->getGroup());
        PHPUnit::assertEquals($month, $nationalComponentStatistics->getMonth());
        PHPUnit::assertEquals($year, $nationalComponentStatistics->getYear());
    }
}
