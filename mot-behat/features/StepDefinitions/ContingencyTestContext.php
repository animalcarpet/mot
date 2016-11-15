<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\ContingencyData;
use Dvsa\Mot\Behat\Support\Data\ContingencyMotTestData;
use Dvsa\Mot\Behat\Support\Data\Params\ContingencyDataParams;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use PHPUnit_Framework_Assert as PHPUnit;

class ContingencyTestContext implements Context
{
    private $siteData;

    private $userData;

    private $motTestData;

    private $contingencyData;

    private $contingencyMotTestData;

    private $vehicleData;

    private $dailyContingencyCode;

    public function __construct(
        SiteData $siteData,
        UserData $userData,
        MotTestData $motTestData,
        ContingencyData $contingencyData,
        ContingencyMotTestData $contingencyMotTestData,
        VehicleData $vehicleData
    )
    {
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->contingencyData = $contingencyData;
        $this->contingencyMotTestData = $contingencyMotTestData;
        $this->vehicleData = $vehicleData;
    }

    /**
     * @When /^I start a Contingency MOT test$/
     */
    public function iStartAContingencyMOTTest()
    {
        $this->contingencyMotTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->create(),
            $this->siteData->get()
        );
    }

    /**
     * @Given I called the helpdesk to ask for a daily contingency code
     */
    public function iCalledHelpdeskToAskForDailyContingencyCode()
    {
        $this->dailyContingencyCode = ContingencyData::CONTINGENCY_CODE;
    }

    /**
     * @When /^I attempt to create a new contingency test with a (.*)$/
     */
    public function iAttemptToCreateANewContingencyTestWithA($contingencyCode)
    {
        try {
            $this->createContingencyCode($contingencyCode, 'SO');
        } catch (UnexpectedResponseStatusCodeException $e) {
            $exception = $e;
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);

    }

    /**
     * @When I create a new contingency test with reason :emergencyCode
     */
    public function iCreateANewContingencyTestWithReason($emergencyCode)
    {
        $this->createContingencyCode(Authentication::CONTINGENCY_CODE_DEFAULT, $emergencyCode);
    }

    /**
     * @Then /^I should receive an emergency log id$/
     */
    public function iShouldReceiveAnEmergencyLogId()
    {
        $emergencyLogId = $this->contingencyData->getEmergencyLogId($this->siteData->get()->getName());
        PHPUnit::assertTrue(is_int($emergencyLogId), 'Emergency log Id is not a number.');
    }

    /**
     * @Given /^the Contingency Test is Logged$/
     */
    public function theContingencyTestIsLogged()
    {
        $motTest = $this->motTestData->getLast();

        $contingencyDto = $this->contingencyData->getBySiteId($this->siteData->get()->getId());
        PHPUnit::assertEquals($contingencyDto->getContingencyCode(), $motTest->getEmergencyLog()[ContingencyDataParams::NUMBER], 'Contingency Code not returned.');
        PHPUnit::assertEquals($this->contingencyData->getEmergencyLogId(), $motTest->getEmergencyLog()[ContingencyDataParams::ID], 'Emergency Log Id not returned.');
    }

    /**
     * @When /^I record a Contingency Test with (.*) at ([0-9]{2}:[0-9]{2}:[0-9]{2}|now)$/
     * @param $date
     * @param $time
     */
    public function iStartAContingencyMOTTestOnDateAtTime($date, $time)
    {
        $dateTime = new DateTime();

        if ($date != 'today') {
            $dateTime->modify($date);
        }

        if ($time != 'now') {
            $timeParts = explode(':', $time);
            $dateTime->setTime($timeParts[0], $timeParts[1], $timeParts[2]);
        }

        $this->contingencyMotTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->createByUser($this->userData->getCurrentLoggedUser()->getAccessToken()),
            $this->siteData->get(),
            ["dateTime" => $dateTime]
        );
    }

    public function createContingencyCode($contingencyCode, $reasonCode)
    {
        $data = [
            ContingencyDataParams::CONTINGENCY_CODE => $contingencyCode,
            ContingencyDataParams::REASON_CODE => $reasonCode,
        ];

        $this->contingencyData->getContingencyCodeID(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get(),
            $data
        );
    }
}