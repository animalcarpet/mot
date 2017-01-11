<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode as Table;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Datasource\Random;
use Dvsa\Mot\Behat\Support\Api\CustomerService;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonContext implements Context
{
    private $personalMotTestingClasses;

    private $personalDashboard;

    private $newEmailAddress;

    private $searchData;

    private $userHelpDeskData;

    private $userId;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var Person
     */
    private $person;

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
    private $updateUserEmailResponse;

    /**
     * @var Response
     */
    private $customerServiceSearchResponse;

    /**
     * @var Response
     */
    private $testerDetailsResponse;

    /**
     * @param CustomerService $customerService
     * @param Person          $person
     */
    public function __construct(CustomerService $customerService, Person $person)
    {
        $this->customerService = $customerService;
        $this->person = $person;
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
     * @When /^I get Information about my MOT Classes$/
     */
    public function iGetInformationAboutMyMOTClasses()
    {
        $this->personalMotTestingClasses = $this->person->getPersonMotTestingClasses(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
    }

    /**
     * @Then /^I will see my Available Classes$/
     */
    public function iWillSeeMyAvailableClasses()
    {
        $classes = $this->personalMotTestingClasses->getBody()['data'];
        foreach ($classes as $class => $status) {
            PHPUnit::assertEquals('QLFD', $status, $class . ' not QLFD');
        }
    }

    /**
     * @When /^I get Information about my Dashboard$/
     */
    public function iGetInformationAboutMyDashboard()
    {
        $this->personalDashboard = $this->person->getPersonDashboard(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
    }

    /**
     * @Then /^I will see my Dashboard Information$/
     */
    public function iWillSeeMyDashboardInformation()
    {
        // Check that the dashboard page returned a 200 status code
        PHPUnit::assertEquals(
            200,
            $this->personalDashboard->getStatusCode(),
            'Non-200 status code returned for dashboard'
        );

        // Check that the hero key in response data is 'tester'
        PHPUnit::assertEquals(
            'tester',
            $this->personalDashboard->getBody()['data']['hero'],
            'Dashboard does not contain Tester'
        );
    }

    /**
     * @Then /^my Dashboard will return the MotTestNumber$/
     */
    public function myDashboardWillReturnTheMotTestNumber()
    {
        PHPUnit::assertTrue(is_numeric($this->motTestContext->getMotTestNumber()), 'MOT test number is not numeric');

        $inProgressTestNumber = $this->personalDashboard->getBody()['data']['inProgressTestNumber'];
        $createdMotTestNumber = $this->motTestContext->getMotTestNumber();
        $message = 'In progress MOT test number %s does not match created MOT test number %s';

        PHPUnit::assertEquals(
            $inProgressTestNumber,
            $createdMotTestNumber,
            sprintf($message, $inProgressTestNumber, $createdMotTestNumber)
        );
    }

    /**
     * @When /^I update my email address on my profile$/
     */
    public function iUpdateMyEmailAddressOnMyProfile()
    {
        $this->newEmailAddress = Random::getRandomEmail();

        $this->updateUserEmailResponse = $this->person->updateUserEmail($this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId(), $this->newEmailAddress);
    }

    /**
     * @Then /^I will see my updated email address$/
     */
    public function iWillSeeMyUpdatedEmailAddress()
    {
        PHPUnit::assertSame(200, $this->updateUserEmailResponse->getStatusCode());
        PHPUnit::assertSame($this->newEmailAddress, $this->updateUserEmailResponse->getBody()['data']['email'], 'Email address on User Profile is incorrect.');
    }

    /**
     * @Then /^my email address will not be updated$/
     */
    public function myEmailAddressWillNotBeUpdated()
    {
        $body = $this->updateUserEmailResponse->getBody()->toArray();

        PHPUnit::assertSame(400, $this->updateUserEmailResponse->getStatusCode(), 'Did not receive 400 Bad Request response');
        PHPUnit::assertFalse(isset($body['data']['email']), 'Data key containing Email data was returned in response body.');
    }

    /**
     * @When /^I update my profile with a mismatching email address$/
     */
    public function iUpdateMyProfileWithAMismatchingEmailAddress()
    {
        $this->newEmailAddress = Random::getRandomEmail();

        //Get a random email address that doesn't match the first
        $emailMismatch = Random::getRandomEmail();

        $this->updateUserEmailResponse = $this->person->updateUserEmail($this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId(), $this->newEmailAddress, $emailMismatch);
    }

    /**
     * @Given /^I should receive an email mismatch message in the response$/
     */
    public function iShouldReceiveAnEmailMismatchMessageInTheResponse()
    {
        $expected = 'Email confirmation does not match the email provided';

        PHPUnit::assertSame(400, $this->updateUserEmailResponse->getStatusCode(), 'Did not receive 400 Bad Request response');
        PHPUnit::assertSame($expected, $this->updateUserEmailResponse->getBody()['errors'][0]['message'], 'Expected error text not returned in response message: '.$expected);
    }

    /**
     * @When /^I update my email address to (.*)$/
     */
    public function iUpdateMyEmailAddressToAnInvalidAddress($email)
    {
        $this->updateUserEmailResponse = $this->person->updateUserEmail(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $email
        );
    }

    /**
     * @Given /^I Search for a Customer Service Operator with following data:$/
     */
    public function iSearchForACustomerServiceOperatorWithFollowingData(Table $table)
    {
        $hash = $table->getColumnsHash();

        foreach ($hash as $row) {
            $this->searchData = [
                'userName' => $row['userName'],
                'firstName' => $row['firstName'],
                'lastName' => $row['lastName'],
                'postCode' => $row['postCode'],
                'dateOfBirth' => $row['dateOfBirth'],
            ];

            $this->customerServiceSearchResponse = $this->customerService->search($this->sessionContext->getCurrentAccessToken(), $this->searchData);
        }
    }

    /**
     * @Then /^the Searched User data will be returned$/
     */
    public function theSearchedUserDataWillBeReturned()
    {
        $response = $this->customerServiceSearchResponse;
        //Check Search Produces valid Results
        PHPUnit::assertEquals(200, $response->getStatusCode(), 'User data not returned,HTTP200 status code not returned in response');

        //Check Results with Searched Data
        if (!empty($this->searchData['firstName'])) {
            PHPUnit::assertEquals($this->searchData['firstName'], $response->getBody()['data'][0]['firstName'], 'First Name');
        }
        if (!empty($this->searchData['lastName'])) {
            PHPUnit::assertEquals($this->searchData['lastName'], $response->getBody()['data'][0]['lastName'], 'Last Name');
        }
        if (!empty($this->searchData['postCode'])) {
            PHPUnit::assertEquals($this->searchData['postCode'], $response->getBody()['data'][0]['postcode'], 'Post Code');
        }
    }

    /**
     * @Then /^the Searched User data will NOT be returned$/
     */
    public function theSearchedUserDataWillNOTBeReturned()
    {
        $response = $this->customerServiceSearchResponse;

        //Check Search Produced Results
        PHPUnit::assertEquals(400, $response->getStatusCode(), 'User data returned, HTTP400 status code not returned in response');

        PHPUnit::assertEquals('Your search returned no results. Add more details and try again.', $response->getBody()['errors'][0]['message'], 'Errors');
    }

    /**
     * @When /^I Search for a (Valid|Invalid) User$/
     *
     * @param $userType
     */
    public function iSearchForAValidUser($userType)
    {
        //todo - Get demotestuser id dynamically - current id is 32
        $this->userId = $userType == 'Valid' ? 132 : 999999;
        $this->userHelpDeskData = $this->customerService->helpDeskProfile($this->sessionContext->getCurrentAccessToken(), $this->userId);
    }

    /**
     * @Then /^the Users data will be returned$/
     */
    public function theUsersDataWillBeReturned()
    {
        PHPUnit::assertEquals(200, $this->userHelpDeskData->getStatusCode(), 'No Search Results Returned, HTTP200 status code not returned in response');
        PHPUnit::assertEquals(Authentication::UNCLAIMED_ACCOUNT, $this->userHelpDeskData->getBody()['data']['userName'], 'Username in User Profile is incorrect');
    }

    /**
     * @Then /^the Users data will not be returned$/
     */
    public function noUserDataWillBeReturned()
    {
        PHPUnit::assertEquals(404, $this->userHelpDeskData->getStatusCode(), 'User data returned in ');
        PHPUnit::assertEquals('Person '.$this->userId.' not found not found', $this->userHelpDeskData->getBody()['errors'][0]['message'], 'Error Message');
    }

    /**
     * @When /^I get my Profile details$/
     */
    public function iGetMyProfileDetails()
    {
        $this->testerDetailsResponse = $this->person->getTesterDetails(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
    }

    /**
     * @Then /^I will see my username in my Profile$/
     */
    public function iWillSeeMyProfileDetails()
    {
        PHPUnit::assertEquals(
            $this->sessionContext->getCurrentUser()->getUsername(),
            $this->testerDetailsResponse->getBody()['data']['username'],
            'Username not returned in response object.'
        );
    }

    /**
     * @Given /^I will see my user id in my Profile$/
     */
    public function iWillSeeMyUserIdInMyProfile()
    {
        PHPUnit::assertEquals(
            $this->sessionContext->getCurrentUserId(),
            $this->testerDetailsResponse->getBody()['data']['id'],
            'User id not returned in response object.'
        );
    }

    /**
     * @Then /^my profile will contain the role "([^"]*)"$/
     *
     * @param $role
     *
     * @internal param $role
     */
    public function myProfileWillContainTheRole($role)
    {
        $roles = $this->testerDetailsResponse->getBody()['data']['roles']->toArray();

        for ($x = 0; $x < count($roles); $x++) {
            if (stristr($roles[$x], $role) == true) {
                return;
            }
        }

        PHPUnit::assertEquals($role, $roles[$x], 'Role not returned in response object: '.$role);
    }
}
