<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\MotTestSearchData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Response;
use TestSupport\Helper\DataGeneratorHelper;
use DvsaCommon\Dto\Search\SearchResultDto;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestHistoryContext implements Context
{
    const USERNAME_PREFIX_LENGTH = 20;

    private $motTest;
    private $tester;
    private $userData;
    private $vehicleData;
    private $motTestData;
    private $motTestSearchData;
    private $siteData;

    /** @var SearchResultDto */
    private $motTestHistory;

    public function __construct(
        MotTest $motTest,
        Tester $tester,
        UserData $userData,
        VehicleData $vehicleData,
        MotTestData $motTestData,
        MotTestSearchData $motTestSearchData,
        SiteData $siteData
    )
    {
        $this->motTest = $motTest;
        $this->tester = $tester;
        $this->userData = $userData;
        $this->vehicleData = $vehicleData;
        $this->motTestData = $motTestData;
        $this->motTestSearchData = $motTestSearchData;
        $this->siteData = $siteData;
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

        $this->motTestHistory = $this->motTestSearchData->searchMotTestHistoryForTester($this->userData->getCurrentLoggedUser(), $this->userData->getLast());
    }

    /**
     * @Then the MOT test history is returned
     */
    public function theMotTestHistoryIsReturned()
    {
        $username = $this->userData->getLast()->getUsername();
        $test = current($this->motTestHistory->getData());

        PHPUnit::assertEquals(1, $this->motTestHistory->getTotalResultCount());
        PHPUnit::assertEquals($username, $test["testerUsername"]);
    }

    /**
     * @When I search for an MOT tests by partial username
     */
    public function iSearchForAnMotTestsByPartialUsername()
    {
        $username = $this->userData->getLast()->getUsername();
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $usernamePrefix = substr($username, 0, self::USERNAME_PREFIX_LENGTH);

        $this->tester->getTesterFull($token, $usernamePrefix);
    }

    /**
     * @Given :number MOT tests have been created by different testers with the same prefix
     */
    public function motTestsHaveBeenCreatedByDifferentTestersWithTheSamePrefix($number)
    {
        $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator([]);
        $baseUsername = $dataGeneratorHelper->generateRandomString(self::USERNAME_PREFIX_LENGTH);
        $suffix = $dataGeneratorHelper->generateRandomString(2);

        while ($number) {
            $username = $baseUsername . str_repeat($suffix, $number);

            $user = $this->userData->createTester($username);
            $vehicle = $this->vehicleData->create();
            $this->motTestData->createPassedMotTest($user, $this->siteData->get(), $vehicle);
            $number--;
        }
    }

    /**
     * @Then the MOT test history is not returned
     */
    public function theMotTestHistoryIsNotReturned()
    {
        $testerFullData = $this->tester->getLastResponse()->getBody()->getData();

        PHPUnit::assertEquals(0, $testerFullData["resultCount"]);
        PHPUnit::assertEquals(0, $testerFullData["totalResultCount"]);
        PHPUnit::assertCount(0, $testerFullData["data"]);
    }

    /**
     * @When /^I search for an MOT tests history by (.*)$/
     */
    public function iSearchForAnMOTTestHistoryBy($type)
    {
        $params = [
            "dateFrom" => null,
            "dateTo" => null,
            "rowCount" => 1,
            "order" => "desc"
        ];

        switch($type) {
            case "site":
                $params["siteNumber"] = $this->siteData->get()->getSiteNumber();
                break;
            case "vin":
                $params["vin"] = $this->vehicleData->getLast()->getVin();
                break;
            case "registration":
                $params["vrm"] = $this->vehicleData->getLast()->getRegistration();
                break;
                break;
            case "testNumber":
                $params["testNumber"] = $this->motTestData->getLast()->getMotTestNumber();
                break;
        }

        $this->motTestSearchData->searchMotTestHistory(
            $this->userData->getCurrentLoggedUser(),
            $params
        );;
    }

    /**
     * @Then /^MOT test history for vehicle and type (.*) is returned$/
     *
     * @param $testType
     */
    public function motTestHistoryForVehicleIsReturned($testType)
    {
        $registration = $this->vehicleData->getLast()->getRegistration();
        $response = $this->motTestSearchData->getLastResponse();
        PHPUnit::assertNotEmpty(
            $this->filterMotHistorySearchResponse($response, $registration, $testType)
        );
    }

    /**
     * @Then /^MOT test history for vehicle and type (.*) is not returned$/
     *
     * @param $testType
     */
    public function motTestHistoryForVehicleIsNotReturned($testType)
    {
        $registration = $this->vehicleData->getLast()->getRegistration();
        $response = $this->motTestSearchData->getLastResponse();
        PHPUnit::assertEmpty(
            $this->filterMotHistorySearchResponse($response, $registration, $testType)
        );
    }

    protected function filterMotHistorySearchResponse(Response $response, $registration, $testType)
    {
        $data = $response->getBody()["data"]["data"]->toArray();

        $result = array_filter($data, function ($value) use ($registration, $testType) {
            return $value["registration"] == $registration &&
            $value["testType"] == $testType;
        });

        return $result;
    }
}
