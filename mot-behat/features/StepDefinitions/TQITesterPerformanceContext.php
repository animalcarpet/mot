<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Generator\TesterPerformanceMotTestGenerator;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\Statistics\TesterPerformanceCalculator;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\MOT\Behat\Support\Data\Params\PersonParams;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\NationalPerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\SitePerformanceApiResource;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\TesterPerformanceApiResource;
use DvsaCommon\Dto\Site\SiteDto;
use PHPUnit_Framework_Assert as PHPUnit;

class TQITesterPerformanceContext implements Context
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
    public function clearCache(BeforeScenarioScope $scope)
    {
        $this->testSupportHelper->getStatisticsAmazonCacheService()->removeAll();
        $this->testSupportHelper->getMotService()->removeAllTestStatistics();
    }

    /**
     * @Given There is a tester :testerName associated with :site1 and :site2
     */
    public function thereIsATesterAssociatedWithAnd($testerName, SiteDto $site1, SiteDto $site2)
    {
        $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$site1->getId(), $site2->getId()]], $testerName);
    }

    /**
     * @Given There is a tester :testerName associated with :site
     */
    public function thereIsATesterAssociatedWith($testerName, SiteDto $site)
    {
        $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$site->getId()]], $testerName);
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

    /** @Given Test Quality Cache is updated between :startMonthAgo and :endMonthsAgo months ago */
    public function testQualityCacheUpdate($startMonthAgo, $endMonthsAgo)
    {
        $this->testSupportHelper->getMotService()->removeAllTestStatistics();
        $areaOfficeUser = $this->userData->createAreaOffice1User();

        for ($i = $startMonthAgo; $i <= $endMonthsAgo; $i++)
        {
            $this->motTestData->generateTQIReport($areaOfficeUser, $i);
        }
    }

    /**
     * @Then I should be able to see the tester performance statistics performed last :months months at site :site
     */
    public function iShouldBeAbleToSeeTheTesterPerformanceStatisticsPerformedLastMonthsAtSite($months, SiteDto $site)
    {
        /** @var SitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(SitePerformanceApiResource::class);
        $actualStats = $apiResource->getForMonthRange($site->getId(), $months);

        $testerPerformanceCalculator = new TesterPerformanceCalculator($this->motTestData->getAll());
        $expectedStats = $testerPerformanceCalculator->calculateTesterPerformanceStatisticsForSite($site->getId(), $months);

        PHPUnit::assertEquals($expectedStats, $actualStats);
    }

    /**
     * @Then I should be able to see national tester performance statistics for last :months months
     */
    public function iShouldBeAbleToSeeNationalTesterPerformanceStatisticsForLastMonths($months)
    {
        $date = new \DateTime();

        $month = (int)$date->format("m");
        $year = (int)$date->format("Y");

        /** @var NationalPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(NationalPerformanceApiResource::class);
        $actualStats = $apiResource->getForMonths($months);

        PHPUnit::assertEquals($month, $actualStats->getMonth());
        PHPUnit::assertEquals($year, $actualStats->getYear());
        PHPUnit::assertEquals($months, $actualStats->getMonthRange());
    }

    /**
     * @Then there is no tester performance statistics performed in last :months months at site :site
     */
    public function thereIsNoTesterPerformanceStatisticsPerformedInLastMonthsAtSite($months, SiteDto $site)
    {
        /** @var SitePerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(SitePerformanceApiResource::class);
        $actualStats = $apiResource->getForMonthRange($site->getId(), $months);

        PHPUnit::assertTrue(empty($actualStats->getA()->getStatistics()));
        PHPUnit::assertTrue(empty($actualStats->getB()->getStatistics()));
    }

    /**
     * @Then I should be able to see the tester performance statistics performed :monthsRange months ago
     */
    public function iShouldBeAbleToSeeTheTesterPerformanceStatisticsPerformedMonthsAgo($monthsRange)
    {

        /** @var TesterPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(TesterPerformanceApiResource::class);
        $actualStats = $apiResource->get($this->userData->getCurrentLoggedUser()->getUserId(), $monthsRange);

        $testerPerformanceCalculator = new TesterPerformanceCalculator($this->motTestData->getAll());
        $expectedStats = $testerPerformanceCalculator->calculateTesterPerformanceStatisticsForTester(
            $this->userData->getCurrentLoggedUser()->getUserId(),
            $monthsRange
        );

        PHPUnit::assertEquals($expectedStats, $actualStats);
    }

    /**
     * @Then there is no tester performance statistics performed :months months ago
     */
    public function thereIsNoTesterPerformanceStatisticsPerformedMonthsAgo($months)
    {
        /** @var TesterPerformanceApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(TesterPerformanceApiResource::class);
        $actualStats = $apiResource->get($this->userData->getCurrentLoggedUser()->getUserId(), $months);

        PHPUnit::assertNull($actualStats->getGroupAPerformance());
        PHPUnit::assertNull($actualStats->getGroupBPerformance());
    }

    /**
         * @Then there are tester performance statistics performed in last :months months at site :site and contains statistics for :testersCount tester for both groups
         * @Then there are tester performance statistics performed in last :months months at site :site and contains statistics for :testersCount testers for both groups
     */
    public function thereIsTesterPerformanceStatisticsPerformedInLastMonthsAtSiteForTesterCount($months, SiteDto $site, $testersCount)
    {
    /** @var SitePerformanceApiResource $apiResource */
    $apiResource = $this->apiResourceHelper->create(SitePerformanceApiResource::class);
    $actualStats = $apiResource->getForMonthRange($site->getId(), $months);

            PHPUnit::assertEquals($testersCount, count($actualStats->getA()->getStatistics()));
            PHPUnit::assertEquals($testersCount, count($actualStats->getB()->getStatistics()));
    }
}
