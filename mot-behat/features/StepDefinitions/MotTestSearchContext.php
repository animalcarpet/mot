<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\MotTestSearchData;
use DvsaCommon\Dto\Common\MotTestDto;
use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestSearchContext implements Context
{
    private $siteData;

    private $userData;

    private $motTestData;

    private $motTestSearchData;

    /**
     * @var Response
     */
    private $searchResponse;

    /**
     * @var DataCollection;
     */
    private $foundedMotTests;

    public function __construct(
        SiteData $siteData,
        UserData $userData,
        MotTestData $motTestData,
        MotTestSearchData $motTestSearchData
    )
    {
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->motTestSearchData = $motTestSearchData;
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
        $motTest = $this->motTestData->getLast();

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
            $this->foundedMotTests = new DataCollection(MotTestDto::class);
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
            $this->foundedMotTests = new DataCollection(MotTestDto::class);
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
