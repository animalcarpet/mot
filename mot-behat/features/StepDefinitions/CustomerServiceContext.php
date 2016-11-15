<?php

use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Api\CustomerService;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode as Table;
use PHPUnit_Framework_Assert as PHPUnit;
use Zend\Http\Response as HttpResponse;

class CustomerServiceContext implements Context
{
    private $customerService;
    private $userData;

    private $userId;
    private $searchData = [];
    /** @var Response */
    private $customerServiceSearchResponse;
    /** @var Response */
    private $userHelpDeskData;

    public function __construct(CustomerService $customerService, UserData $userData)
    {
        $this->customerService = $customerService;
        $this->userData = $userData;
    }

    /**
     * @Given /^I Search for a Customer Service Operator with following data:$/
     */
    public function iSearchForACustomerServiceOperatorWithFollowingData(Table $table)
    {
        $hash = $table->getColumnsHash();

        foreach ($hash as $row) {
            $this->searchData = [
                PersonParams::USER_NAME => $row[PersonParams::USER_NAME],
                PersonParams::FIRST_NAME => $row[PersonParams::FIRST_NAME],
                PersonParams::LAST_NAME => $row[PersonParams::LAST_NAME],
                PersonParams::POST_CODE => $row[PersonParams::POST_CODE],
                PersonParams::DATE_OF_BIRTH => $row[PersonParams::DATE_OF_BIRTH],
                PersonParams::EMAIL => $row[PersonParams::EMAIL],
            ];

            $user = $this->userData->getCurrentLoggedUser();
            $this->customerServiceSearchResponse = $this->customerService->search($user->getAccessToken(), $this->searchData);
        }
    }

    /**
     * @Given I try Search for a Customer Service Operator with following data:
     */
    public function iTrySearchForACustomerServiceOperatorWithFollowingData(Table $table)
    {
        try {
            $this->iSearchForACustomerServiceOperatorWithFollowingData($table);
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->customerServiceSearchResponse = $this->customerService->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }


    /**
     * @Then /^the Searched User data will be returned$/
     */
    public function theSearchedUserDataWillBeReturned()
    {
        $response = $this->customerServiceSearchResponse;
        //Check Search Produces valid Results
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode(), 'User data not returned,HTTP200 status code not returned in response');

        //Check Results with Searched Data
        if (!empty($this->searchData[PersonParams::FIRST_NAME])) {
            PHPUnit::assertEquals($this->searchData[PersonParams::FIRST_NAME], $response->getBody()->getData()[0][PersonParams::FIRST_NAME], 'First Name');
        }
        if (!empty($this->searchData[PersonParams::LAST_NAME])) {
            PHPUnit::assertEquals($this->searchData[PersonParams::LAST_NAME], $response->getBody()['data'][0][PersonParams::LAST_NAME], 'Last Name');
        }
        if (!empty($this->searchData[PersonParams::POST_CODE])) {
            PHPUnit::assertEquals($this->searchData[PersonParams::POST_CODE], $response->getBody()['data'][0]['postcode'], 'Post Code');
        }
    }

    /**
     * @Then /^the Searched User data will NOT be returned$/
     */
    public function theSearchedUserDataWillNOTBeReturned()
    {
        $response = $this->customerServiceSearchResponse;

        //Check Search Produced Results
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $response->getStatusCode(), 'User data returned, HTTP400 status code not returned in response');

        PHPUnit::assertEquals('Your search returned no results. Add more details and try again.', $response->getBody()->getErrors()[0]['message'], 'Errors');
    }

    /**
     * @When /^I Search for a Valid User$/
     */
    public function iSearchForAValidUser()
    {
        $this->userId = $this->userData->createTester()->getUserId();
        $this->userHelpDeskData = $this->customerService->helpDeskProfile(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userId
        );
    }

    /**
     * @When /^I Search for a Invalid User$/
     */
    public function iSearchForAInvalidUser()
    {
        try {
            $this->userId = 999999;
            $this->userHelpDeskData = $this->customerService->helpDeskProfile(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->userId
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->userHelpDeskData = $this->customerService->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then /^the Users data will be returned$/
     */
    public function theUsersDataWillBeReturned()
    {
        $tester = $this->userData->get(UserData::DEFAULT_TESTER_NAME);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->userHelpDeskData->getStatusCode(), 'No Search Results Returned, HTTP200 status code not returned in response');
        PHPUnit::assertEquals($tester->getUsername(), $this->userHelpDeskData->getBody()['data']['userName'], 'Username in User Profile is incorrect');
    }

    /**
     * @Then /^the Users data will not be returned$/
     */
    public function noUserDataWillBeReturned()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_404, $this->userHelpDeskData->getStatusCode(), 'User data returned in ');
        PHPUnit::assertEquals('Person '.$this->userId.' not found not found', $this->userHelpDeskData->getBody()->getErrors()[0]['message'], 'Error Message');
    }
}