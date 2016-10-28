<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestHistoryContext implements Context
{
    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var PersonContext
     */
    private $personContext;

    /**
     * @var Tester
     */
    private $tester;

    private $userData;

    private $motTestHistory = [];

    public function __construct(MotTest $motTest, Tester $tester, UserData $userData)
    {
        $this->motTest = $motTest;
        $this->tester = $tester;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
    }

    /**
     * @When I search for an MOT tests by username
     */
    public function iSearchForAnMotTestsByUsername()
    {
        $username = $this->userData->getLast()->getUsername();
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();

        $response = $this->tester->getTesterFull($token, $username);
        $testerFullData = $response->getBody()->getData();

        PHPUnit::assertEquals(1, $testerFullData["resultCount"]);
        PHPUnit::assertEquals(1, $testerFullData["totalResultCount"]);
        PHPUnit::assertCount(1, $testerFullData["data"]);
        PHPUnit::assertEquals(1, preg_match("/" . $username . ",/", current($testerFullData["data"])));

        $response = $this->motTest->searchMotTestHistory($token, ["tester" => $this->userData->getLast()->getUserId()]);
        $this->motTestHistory = $response->getBody()->toArray()["data"];
    }

    /**
     * @Then the MOT test history is returned
     */
    public function theMotTestHistoryIsReturned()
    {
        PHPUnit::assertNotEmpty($this->motTestHistory);

        $username = $this->userData->getLast()->getUsername();
        $test = current($this->motTestHistory["data"]);

        PHPUnit::assertCount(1, $this->motTestHistory["data"]);
        PHPUnit::assertEquals($username, $test["testerUsername"]);
    }

    /**
     * @When I search for an MOT tests by partial username
     */
    public function iSearchForAnMotTestsByPartialUsername()
    {
        $username = $this->userData->getLast()->getUsername();
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $usernamePrefix = substr($username, 0, MotTestContext::USERNAME_PREFIX_LENGTH);

        $response = $this->tester->getTesterFull($token, $usernamePrefix);
        $testerFullData = $response->getBody()->getData();

        PHPUnit::assertEquals(0, $testerFullData["resultCount"]);
        PHPUnit::assertEquals(0, $testerFullData["totalResultCount"]);
        PHPUnit::assertCount(0, $testerFullData["data"]);
    }

    /**
     * @Then the MOT test history is not returned
     */
    public function theMotTestHistoryIsNotReturned()
    {
        PHPUnit::assertEmpty($this->motTestHistory);
    }
}
