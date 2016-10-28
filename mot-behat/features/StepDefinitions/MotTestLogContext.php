<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\SlotReport;
use PHPUnit_Framework_Assert as PHPUnit;
use Dvsa\Mot\Behat\Support\Response;

class MotTestLogContext implements Context
{

    /**
     * @var AuthorisedExaminer
     */
    private $authorisedExaminer;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var SiteData
     */
    private $siteData;

    private $authorisedExaminerData;

    private $userData;

    private $slotReport;

    private $aeTestLogs = [];

    private $siteTestLogs = [];

    /**
     * @var Response
     */
    private $userTestLogs;


    public function __construct(
        AuthorisedExaminer $authorisedExaminer,
        MotTest $motTest,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData,
        SlotReport $slotReport
    ) {
        $this->authorisedExaminer = $authorisedExaminer;
        $this->motTest = $motTest;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->slotReport = $slotReport;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @When I download my test logs for today
     */
    public function getTestLogs()
    {
        $this->userTestLogs = $this->authorisedExaminer->getTodaysTestLogs(
            $this->sessionContext->getCurrentAccessToken(),
            2
        );
    }

    /**
     * @When I download that site's test logs for today
     */
    public function getSiteTestLogs()
    {
        $this->userTestLogs = $this->authorisedExaminer->getTodaysTestLogs(
            $this->sessionContext->getCurrentAccessToken(),
            $this->siteData->get()->getId(),
            $this->siteData->get()->getOrganisation()->getId()
        );
    }

    /**
     * @Then /^I will see the correct MOT Test Log Data$/
     */
    public function iWillSeeTheCorrectMOTTestLogData()
    {
        PHPUnit::assertEquals(200, $this->userTestLogs->getStatusCode());
        $result = json_decode($this->userTestLogs->getBody()['data']['data'], true);

        foreach ($result as $motTestNumber => $motTestLogData) {
            // Retrieve the MOT test by MOT test number
            $response = $this->motTest->getMotData($this->sessionContext->getCurrentAccessToken(), $motTestNumber);
            $motTestData = $response->getBody()['data'];

            PHPUnit::assertEquals($motTestNumber, $motTestData['motTestNumber']);
            PHPUnit::assertEquals($motTestLogData['vehicleVRM'], $motTestData['registration']);
            PHPUnit::assertEquals($motTestLogData['vehicleVIN'], $motTestData['vin']);
            PHPUnit::assertEquals(
                (new \DateTime($motTestData['completedDate']))->format('dmY'),
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

        $this->aeTestLogs = [
            "some" => $this->authorisedExaminerData->getTodaysTestLogs($this->userData->getCurrentLoggedUser(), $someAe),
            "other" => $this->authorisedExaminerData->getTodaysTestLogs($this->userData->getCurrentLoggedUser(), $otherAe),
        ];

        $this->siteTestLogs = [
            "some site" => $this->siteData->getTestLogs($this->userData->getCurrentLoggedUser(), $this->siteData->get("some site"))

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
            $this->sessionContext->getCurrentAccessToken(),
            $this->siteData->get("some site")->getId(),
            $this->authorisedExaminerData->get("some")->getId()
        );

        $slotUsageNumber = $response->getBody()->getData()["slot_usage_number"];

        PHPUnit::assertEquals(2, $slotUsageNumber);
    }
}
