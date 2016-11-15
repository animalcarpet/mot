<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use DvsaCommon\Dto\Search\SearchResultDto;;
use DvsaCommon\Dto\Organisation\MotTestLogSummaryDto;
use Dvsa\Mot\Behat\Support\Api\SlotReport;
use DvsaCommon\Utility\DtoHydrator;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestLogContext implements Context
{
    private $tester;

    private $siteData;

    private $authorisedExaminerData;

    private $userData;

    private $motTestData;

    private $slotReport;

    private $aeTestLogs = [];

    private $siteTestLogs = [];

    /** @var SearchResultDto */
    private $userTestLogs;

    /** @var MotTestLogSummaryDto */
    private $userTestLogsSummary;


    public function __construct(
        Tester $tester,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData,
        MotTestData $motTestData,
        SlotReport $slotReport
    ) {
        $this->tester = $tester;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->slotReport = $slotReport;
    }

    /**
     * @When I download my test logs for today
     */
    public function iDownloadMyTestLogsForToday()
    {
        $this->userTestLogs = $this->authorisedExaminerData->getTodaysTestLogs(
            $this->userData->getCurrentLoggedUser(),
            $this->authorisedExaminerData->get()
        );
    }

    /**
     * @When I download that site's test logs for today
     */
    public function getSiteTestLogs()
    {
        $this->userTestLogs = $this->authorisedExaminerData->getTodaysSiteTestLogs(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get()
        );

    }

    /**
     * @Then /^I will see the correct MOT Test Log Data$/
     */
    public function iWillSeeTheCorrectMOTTestLogData()
    {
        $data = $this->userTestLogs->getData();
        PHPUnit::assertNotEmpty($data);

        foreach ($data as $motTestNumber => $motTestLogData) {
            $motTestData = $this->motTestData->fetchMotTestData($this->userData->getCurrentLoggedUser(), $motTestNumber);

            PHPUnit::assertEquals($motTestNumber, $motTestData->getMotTestNumber());
            PHPUnit::assertEquals($motTestLogData['vehicleVRM'], $motTestData->getRegistration());
            PHPUnit::assertEquals($motTestLogData['vehicleVIN'], $motTestData->getVin());
            PHPUnit::assertEquals(
                (new \DateTime($motTestData->getCompletedDate()))->format('dmY'),
                (new \DateTime())->format('dmY')
            );
        }
    }

    /**
     * @When I fetch test logs for those AE and VTS's
     */
    public function iFetchTestLogsForThoseAeAndVtsS()
    {
        $someAe = $this->authorisedExaminerData->get("some");
        $otherAe = $this->authorisedExaminerData->get("other");

        $someTestLogs = $this->authorisedExaminerData->getTodaysTestLogs($this->userData->getCurrentLoggedUser(), $someAe);
        $otherTestLogs = $this->authorisedExaminerData->getTodaysTestLogs($this->userData->getCurrentLoggedUser(), $otherAe);

        $this->aeTestLogs = [
            "some" => $someTestLogs->getResultCount(),
            "other" => $otherTestLogs->getResultCount(),
        ];

        $someSiteTestLogs = $this->siteData->getTestLogs($this->userData->getCurrentLoggedUser(), $this->siteData->get("some site"));
        $this->siteTestLogs = [
            "some site" => $someSiteTestLogs

        ];
    }

    /**
     * @Then test logs show correct test count
     */
    public function testLogsShowCorrectTestCount()
    {
        PHPUnit::assertEquals(2, $this->aeTestLogs["some"]);
        PHPUnit::assertEquals(1, $this->aeTestLogs["other"]);
        PHPUnit::assertEquals(2, $this->siteTestLogs["some site"]);
    }

    /**
     * @Then slot usage shows correct value
     */
    public function slotUsageShowsCorrectValue()
    {
        $response = $this->slotReport->getSlotUsage(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->authorisedExaminerData->get("some")->getId()
        );

        $rows = $response->getBody()->getData()["rows"];

        PHPUnit::assertCount(1, $rows);

        $data = array_shift($rows);

        PHPUnit::assertEquals(2, $data["tests_number"]);

        $response = $this->slotReport->getSlotUsage(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->authorisedExaminerData->get("other")->getId()
        );

        $rows = $response->getBody()->getData()["rows"];

        PHPUnit::assertCount(1, $rows);

        $data = array_shift($rows);

        PHPUnit::assertEquals(1, $data["tests_number"]);

        $response = $this->slotReport->getSlotUsageNumber(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->siteData->get("some site")->getId(),
            $this->authorisedExaminerData->get("some")->getId()
        );

        $slotUsageNumber = $response->getBody()->getData()["slot_usage_number"];

        PHPUnit::assertEquals(2, $slotUsageNumber);
    }

    /**
     * @When I review my test logs
     */
    public function iReviewMyTestLogs()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $response = $this->tester->getTesterTestLogs(
            $user->getAccessToken(),
            $user->getUserId()
        );

        $this->userTestLogs = DtoHydrator::jsonToDto($response->getBody()->getData());

        $response = $this->tester->getTesterTestLogsSummary(
            $user->getAccessToken(),
            $user->getUserId()
        );

        $this->userTestLogsSummary = DtoHydrator::jsonToDto($response->getBody()->getData());
    }

    /**
     * @Then /^([1-9]*) test logs should show today in summary section$/
     */
    public function TestLogsShouldShowTodayInSummarySection($number)
    {
        PHPUnit::assertEquals($number, $this->userTestLogsSummary->getToday());
    }

    /**
     * @Then /^My test logs should return ([1-9]*) detailed records$/
     */
    public function MyTestLogsShouldReturnDetailedRecords($number)
    {
        PHPUnit::assertEquals($number, $this->userTestLogs->getResultCount());
    }

}
