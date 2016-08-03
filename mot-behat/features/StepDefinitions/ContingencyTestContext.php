<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;

class ContingencyTestContext implements Context
{
    /**
     * @var ContingencyTest
     */
    private $contingencyTest;

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
    public function __construct(ContingencyTest $contingencyTest)
    {
        $this->contingencyTest = $contingencyTest;
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
        $this->dailyContingencyCode = '12345A';
    }

    /**
     * @When /^I create a new contingency test$/
     */
    public function iCreateANewContingencyTest()
    {
        $this->createContingencyCode($this->dailyContingencyCode, 'SO');
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
     * @When /^I create a new contingency test with a Contingency Code and Reason (.*) (.*)$/
     */
    public function iCreateANewContingencyTestWithAContingencyCodeAndReason($contingencyCode, $reason)
    {
        $this->createContingencyCode($contingencyCode, $reason);
    }

    /**
     * @When /^I create a new contingency test with reason (.*)$/
     */
    public function iCreateANewContingencyTestWithReason($reason)
    {
        $this->createContingencyCode(Authentication::CONTINGENCY_CODE_DEFAULT, $reason);
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
        $siteId = 1
    ) {
        $this->contingencyData = [
            'contingencyCode' => $contingencyCode,
            'reasonCode' => $reasonCode,
        ];

        if ($token === null) {
            $token = $this->sessionContext->getCurrentAccessTokenOrNull();
        }

        $this->createContingencyCodeIdResponse = $this->contingencyTest->getContingencyCodeID(
            $token,
            $this->contingencyData['contingencyCode'],
            $this->contingencyData['reasonCode'],
            $dateTime,
            $siteId
        );
    }

    /**
     * @return string
     */
    public function getContingencyCode()
    {
        if (!isset($this->contingencyData['contingencyCode'])) {
            throw new \LogicException('No contingency code was set');
        }

        return (string) $this->contingencyData['contingencyCode'];
    }

    /**
     * @return int
     */
    public function getEmergencyLogId()
    {
        if (200 !== $this->createContingencyCodeIdResponse->getStatusCode()) {
            throw new \LogicException('Failed to get the contingency code');
        }

        return (int) $this->createContingencyCodeIdResponse->getBody()['data']['emergencyLogId'];
    }
}