<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\MotTestSearchData;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Collection\Collection;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestSearchContext implements Context
{
    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var SiteData
     */
    private $siteData;

    private $userData;

    private $motTestData;

    private $motTestSearchData;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var MotTestContext
     */
    private $motTestContext;

    /**
     * @var Response
     */
    private $searchResponse;

    /**
     * @var Collection;
     */
    private $foundedMotTests;

    public function __construct(
        MotTest $motTest,
        SiteData $siteData,
        UserData $userData,
        MotTestData $motTestData,
        MotTestSearchData $motTestSearchData
    )
    {
        $this->motTest = $motTest;
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->motTestSearchData = $motTestSearchData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
    }

    /**
     * @When I search for an MOT test
     */
    public function iSearchForAnMOTTest()
    {
        $this->foundedMotTests = $this->motTestSearchData->searchBySiteNumber(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get()->getSiteNumber()
        );
    }

    /**
     * @When I search for an Invalid MOT test
     */
    public function iSearchForAnInvalidMOTTest()
    {
        $this->foundedMotTests = $this->motTestSearchData->searchBySiteNumber(
            $this->userData->getCurrentLoggedUser(),
            'abcdefghijklmnopqrstuvwxyz'
        );
    }

    /**
     * @Then the MOT test data is returned
     */
    public function theMOTTestDataIsReturned()
    {
        /** @var MotTestDto $motTest */
        $motTest = $this->motTestData->getAll()->last();

        /** @var MotTestDto $foundedMotTest */
        $foundedMotTest = $this->foundedMotTests->get($motTest->getMotTestNumber());

        PHPUnit::assertEquals($motTest->getMotTestNumber(), $foundedMotTest->getMotTestNumber());
    }

    /**
     * @Then the MOT test is not found
     */
    public function theMOTTestIsNotFound()
    {
        PHPUnit::assertCount(0, $this->foundedMotTests);
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
                $params["vin"] = "VIN123456789";
                break;
            case "registration":
                $params["vrm"] = "ABCD123";
                break;
                break;
            case "testNumber":
                $params["testNumber"] = $this->motTestContext->getMotTestNumber();
                break;
        }

        $this->searchResponse = $this->motTest->searchMotTestHistory(
            $this->sessionContext->getCurrentAccessToken(),
            $params
        );

    }

    /**
     * @Then /^MOT test history for vehicle and type (.*) is returned$/
     *
     * @param $testType
     */
    public function motTestHistoryForVehicleIsReturned($testType)
    {
        PHPUnit::assertNotEmpty(
            $this->filterMotHistorySearchResponse($this->searchResponse, "ABCD123", $testType)
        );
    }

    /**
     * @Then /^MOT test history for vehicle and type (.*) is not returned$/
     *
     * @param $testType
     */
    public function motTestHistoryForVehicleIsNotReturned($testType)
    {
        PHPUnit::assertEmpty(
            $this->filterMotHistorySearchResponse($this->searchResponse, "ABCD123", $testType)
        );
    }

    protected function filterMotHistorySearchResponse($response, $registration, $testType)
    {
        $data = $response->getBody()["data"]["data"]->toArray();

        $result = array_filter($data, function ($value) use ($registration, $testType) {
            return $value["registration"] == $registration &&
            $value["testType"] == $testType;
        });

        return $result;
    }

    /**
     * @When /^I search for an MOT test with invalid Mot test number$/
     */
    public function iSearchForAnMOTTestWithInvalidMotTestNumber()
    {
        try {
            $this->foundedMotTests = $this->motTestSearchData->searchByTestNumber(
                $this->userData->getCurrentLoggedUser(),
                ''
            );
        } catch (\Exception $e) {
            $this->foundedMotTests = new Collection(MotTestDto::class);
        }

    }

    /**
     * @When /^I search for an MOT test with missing VRM$/
     */
    public function iSearchForAnMOTTestWithMissingVRM()
    {
        try {
            $this->foundedMotTests = $this->motTestSearchData->searchByVehicleRegNr(
                $this->userData->getCurrentLoggedUser(),
                ''
            );
        } catch (\Exception $e) {
            $this->foundedMotTests = new Collection(MotTestDto::class);
        }

    }

    /**
     * @Then the search is failed
     */
    public function theSearchIsFailedWithError()
    {
        PHPUnit::assertCount(0, $this->foundedMotTests);
    }

    /**
     * @When /^I search for an MOT test with non\-existing VRM$/
     */
    public function iSearchForAnMOTTestWithNonExistingVRM()
    {
        $this->foundedMotTests = $this->motTestSearchData->searchByVehicleRegNr(
            $this->userData->getCurrentLoggedUser(),
            'YYYYYY'
        );
    }

    /**
     * @Then /^the search will return no mot test$/
     */
    public function theSearchWillReturnNoMotTest()
    {
        $ResponseMessage = $this->searchResponse->getBody()['data']['resultCount'];
        PHPUnit::assertEquals($ResponseMessage, "0");
    }

    /**
     * @When /^I search for an MOT test with non\-existing mot test number$/
     */
    public function iSearchForAnMOTTestWithNonExistingMotTestNumber()
    {
        $this->foundedMotTests = $this->motTestSearchData->searchByTestNumber(
            $this->userData->getCurrentLoggedUser(),
            '0000000000000'
        );
    }
}
