<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\ContingencyData;
use Dvsa\Mot\Behat\Support\Data\Params\ContingencyDataParams;
use PHPUnit_Framework_Assert as PHPUnit;

class ContingencyTestContext implements Context
{
    /**
     * @var ContingencyTest
     */
    private $contingencyTest;

    /**
     * @var SiteData
     */
    private $siteData;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var Response
     */
    private $createContingencyCodeIdResponse;

    /**
     * @var array
     */
    private $contingencyData;

    /**
     * @var string
     */
    private $dailyContingencyCode;

    /**
     * @param ContingencyTest $contingencyTest
     */
    public function __construct(ContingencyTest $contingencyTest, SiteData $siteData)
    {
        $this->contingencyTest = $contingencyTest;
        $this->siteData = $siteData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Given I called the helpdesk to ask for a daily contingency code
     */
    public function iCalledHelpdeskToAskForDailyContingencyCode()
    {
        $this->dailyContingencyCode = ContingencyData::CONTINGENCY_CODE;
    }

    /**
     * @When /^I attempt to create a new contingency test$/
     */
    public function iAttemptToCreateANewContingencyTest()
    {
        $this->createContingencyCode($this->dailyContingencyCode, 'SO');
    }

    /**
     * @When /^I attempt to create a new contingency test with a (.*)$/
     */
    public function iAttemptToCreateANewContingencyTestWithA($contingencyCode)
    {
        $this->createContingencyCode($contingencyCode, 'SO');
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
        PHPUnit::assertTrue(is_int($this->getEmergencyLogId()), 'Emergency log Id is not a number.');
    }

    /**
     * @param string $testType
     * @param string $contingencyCode
     * @param string $reasonCode
     */
    public function createContingencyCode(
        $contingencyCode = Authentication::CONTINGENCY_CODE_DEFAULT,
        $reasonCode = 'SO',
        DateTime $dateTime = null,
        $token = null,
        $siteId = null
    ) {
        $this->contingencyData = [
            ContingencyDataParams::CONTINGENCY_CODE => $contingencyCode,
            ContingencyDataParams::REASON_CODE => $reasonCode,
        ];

        if ($token === null) {
            $token = $this->sessionContext->getCurrentAccessTokenOrNull();
        }

        if ($siteId === null) {
            $siteId = $this->siteData->get()->getId();
        }

        $this->createContingencyCodeIdResponse = $this->contingencyTest->getContingencyCodeID(
            $token,
            $this->contingencyData[ContingencyDataParams::CONTINGENCY_CODE],
            $this->contingencyData[ContingencyDataParams::REASON_CODE],
            $dateTime,
            $siteId
        );
    }

    /**
     * @return string
     */
    public function getContingencyCode()
    {
        if (!isset($this->contingencyData[ContingencyDataParams::CONTINGENCY_CODE])) {
            throw new \LogicException('No contingency code was set');
        }

        return (string) $this->contingencyData[ContingencyDataParams::CONTINGENCY_CODE];
    }

    /**
     * @return int
     */
    public function getEmergencyLogId()
    {
        if (200 !== $this->createContingencyCodeIdResponse->getStatusCode()) {
            throw new \LogicException('Failed to get the contingency code');
        }

        return (int) $this->createContingencyCodeIdResponse->getBody()->getData()[ContingencyDataParams::EMERGENCY_LOG_ID];
    }
}