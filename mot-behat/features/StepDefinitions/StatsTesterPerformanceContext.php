<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Generator\TesterPerformanceMotTestGenerator;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\Statistics\TesterPerformanceCalculator;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Dto\Site\SiteDto;
use PHPUnit_Framework_Assert as PHPUnit;


class StatsTesterPerformanceContext implements Context
{
    private $siteData;
    private $vehicleData;
    private $motTestData;
    private $userData;
    private $testSupportHelper;
    private $apiResourceHelper;

    public function __construct(
        SiteData $siteData,
        VehicleData $vehicleData,
        MotTestData $motTestData,
        UserData $userData,
        TestSupportHelper $testSupportHelper,
        ApiResourceHelper $apiResourceHelper
    )
    {
        $this->siteData = $siteData;
        $this->vehicleData = $vehicleData;
        $this->motTestData = $motTestData;
        $this->userData = $userData;
        $this->testSupportHelper = $testSupportHelper;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /** @BeforeScenario @test-quality-information */
    public function clearAmazonCache(BeforeScenarioScope $scope)
    {
        $this->testSupportHelper->getStatisticsAmazonCacheService()->removeAll();
    }

    /**
     * @Given There is a tester :testerName associated with :site1 and :site2
     */
    public function thereIsATesterAssociatedWithAnd($testerName, SiteDto $site1, SiteDto $site2)
    {
        $this->userData->createTester(["siteIds" => [$site1->getId(), $site2->getId()]], $testerName);
    }

    /**
     * @Given There is a tester :testerName associated with :site
     */
    public function thereIsATesterAssociatedWith($testerName, SiteDto $site)
    {
        $this->userData->createTester(["siteIds" => [$site->getId()]], $testerName);
    }

    /**
     * @When I am logged in as a Tester :user
     */
    public function iAmLoggedInAsATester(AuthenticatedUser $user)
    {
        $this->userData->setCurrentLoggedUser($user);
    }

    /**
     * @Given there are tests performed at site :site by :tester
     */
    public function thereAreTestsPerformedAtSiteBy(SiteDto $site, AuthenticatedUser $tester)
    {
        $motTestGenerator = new TesterPerformanceMotTestGenerator($this->motTestData, $this->vehicleData);
        $motTestGenerator->generate($site, $tester);
    }

    /**
     * @Then I should be able to see the tester performance statistics performed :months months ago at site :site
     */
    public function iShouldBeAbleToSeeTheTesterPerformanceStatisticsPerformedMonthsAgoAtSite($months, SiteDto $site)
    {
        $interval = sprintf("P%sM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var SitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(SitePerformanceApiResource::class);
        $actualStats = $apiResource->getForDate($site->getId(), $date->format("m"), $date->format("Y"));

        $testerPerformanceCalculator = new TesterPerformanceCalculator($this->motTestData->getAll());
        $expectedStats = $testerPerformanceCalculator->calculateTesterPerformanceStatisticsForSite($site->getId(), $months);

        PHPUnit::assertEquals($expectedStats, $actualStats);
    }

    /**
     * @Then I should be able to see national tester performance statistics for performed :months months ago
     */
    public function iShouldBeAbleToSeeNationalTesterPerformanceStatisticsForPerformedMonthsAgo($months)
    {
        $interval = sprintf("P%dM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        $month = (int)$date->format("m");
        $year = (int)$date->format("Y");

        /** @var NationalPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(NationalPerformanceApiResource::class);
        $actualStats = $apiResource->getForDate($date->format("m"), $date->format("Y"));

        PHPUnit::assertEquals($month, $actualStats->getMonth());
        PHPUnit::assertEquals($year, $actualStats->getYear());
    }

    /**
     * @Then there is no tester performance statistics performed :months months ago at site :site
     */
    public function thereIsNoTesterPerformanceStatisticsPerformedMonthsAgoAtSite($months, SiteDto $site)
    {
        $interval = sprintf("P%dM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var SitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(SitePerformanceApiResource::class);
        $actualStats = $apiResource->getForDate($site->getId(), $date->format("m"), $date->format("Y"));

        PHPUnit::assertTrue(empty($actualStats->getA()->getStatistics()));
        PHPUnit::assertTrue(empty($actualStats->getB()->getStatistics()));
    }

    /**
     * @Then I should be able to see the tester performance statistics performed :months months ago
     */
    public function iShouldBeAbleToSeeTheTesterPerformanceStatisticsPerformedMonthsAgo($months)
    {
        $interval = sprintf("P%dM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var TesterPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(TesterPerformanceApiResource::class);
        $actualStats = $apiResource->get($this->userData->getCurrentLoggedUser()->getUserId(), $date->format("m"), $date->format("Y"));

        $testerPerformanceCalculator = new TesterPerformanceCalculator($this->motTestData->getAll());
        $expectedStats = $testerPerformanceCalculator->calculateTesterPerformanceStatisticsForTester(
            $this->userData->getCurrentLoggedUser()->getUserId(),
            $months
        );

        PHPUnit::assertEquals($expectedStats, $actualStats);
    }

    /**
     * @Then there is no tester performance statistics performed :months months ago
     */
    public function thereIsNoTesterPerformanceStatisticsPerformedMonthsAgo($months)
    {
        $interval = sprintf("P%dM", $months);
        $date = (new \DateTime())->sub(new \DateInterval($interval));

        /** @var TesterPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(TesterPerformanceApiResource::class);
        $actualStats = $apiResource->get($this->userData->getCurrentLoggedUser()->getUserId(), $date->format("m"), $date->format("Y"));

        PHPUnit::assertNull($actualStats->getGroupAPerformance());
        PHPUnit::assertNull($actualStats->getGroupBPerformance());
    }
}
