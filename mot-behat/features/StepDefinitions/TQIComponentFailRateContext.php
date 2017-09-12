<?php

use Behat\Behat\Context\Context;
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

class TQIComponentFailRateContext implements Context
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
        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $months);

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
        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $componentBreakdown = $apiResource->getForTesterAtSite($site->getId(), $tester->getUserId(), $group, $months);

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
        /** @var NationalComponentStatisticApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(NationalComponentStatisticApiResource::class);
        $nationalComponentStatistics = $apiResource->getForDate($group, $months);

        PHPUnit::assertEquals($group, $nationalComponentStatistics->getGroup());
        PHPUnit::assertEquals($months, $nationalComponentStatistics->getMonthRange());
    }

    /**
     * @Then I should be able to see component fail rate statistics performed up to :months months for all testers at site :site for group :group
     */
    public function iShouldBeAbleToSeeComponentFailRateStatisticsPerformedUpToMonthsForAllTestersAtSiteForGroup($months, SiteDto $site, $group)
    {
        /** @var ComponentFailRateApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(ComponentFailRateApiResource::class);
        $allComponentBreakdowns = $apiResource->getForAllTestersAtSite($site->getId(), $group, $months);

        $this->assertComponentBreakdownForTestersExists($this->getTesters(), $allComponentBreakdowns);
    }

    /**
     * @return AuthenticatedUser[]
     */
    private function getTesters(): array
    {
       return [
           $this->userData->get("Kowalsky"),
           $this->userData->get("Sikorsky")
       ];
    }

    /**
     * @param AuthenticatedUser[] $testers
     * @param ComponentBreakdownDto[] $allComponentBreakdowns
     */
    private function assertComponentBreakdownForTestersExists(array $testers, array $allComponentBreakdowns) {
        $testerUsernamesInComponentBreakdown = [];
        foreach ($allComponentBreakdowns as $componentBreakdown) {
            $testerUsernamesInComponentBreakdown[] = $componentBreakdown->getUserName();
        }

        foreach ($testers as $tester) {
            PHPUnit::assertContains($tester->getUsername(), $testerUsernamesInComponentBreakdown);
        }
    }
}

