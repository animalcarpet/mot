<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode as Table;
use Dvsa\Mot\Behat\Support\Api\EmailDuplicate;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Datasource\Random;
use Dvsa\Mot\Behat\Support\Api\AuthorisedExaminer;
use Dvsa\Mot\Behat\Support\Api\CustomerService;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Dvsa\Mot\Behat\Support\Api\Vts;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    const FORBIDDEN = "FORBIDDEN";
    const PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID = 26;

    private $personalMotTestingClasses;

    private $personalDashboard;

    private $newEmailAddress;

    private $newName;

    private $newAddress;

    private $searchData;

    private $userHelpDeskData;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var Zend\View\Model\JsonModel
     */
    private $personLoginData;

    /**
     * @var Response
     */
    private $userTestLogsSummary;

    /**
     * @var Response
     */
    private $userTestLogs;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Tester
     */
    private $tester;

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
    private $updateNameResponse;

    /**
     * @var Response
     */
    private $updateAddressResponse;

    /**
     * @var Response
     */
    private $customerServiceSearchResponse;

    /**
     * @var Response
     */
    private $testerDetailsResponse;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var Response
     */
    private $testerQualificationResponse;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var VtsContext
     */
    private $vtsContext;

    /**
     * @var Vts
     */
    private $vts;

    /**
     * @var AuthorisedExaminerContext
     */
    private $authorisedExaminerContext;

    /**
     * @var AuthorisedExaminer
     */
    private $authorisedExaminer;


    private $newDateOfBirth;

    /**
     * @var Response
     */
    private $updateDateOfBirthResponse;

    /**
     * @var array
     */
    private $newTelephoneNumber;

    /**
     * @var Response
     */
    private $updateTelephoneNumberResponse;

    private $ae;

    /**
     * @var Response
     */
    private $personStats;

    /**
     * @var Notification
     */
    private $notification;

    private $users = [];

    private $authorisedExaminerData;

    private $siteData;

    /** @var  EmailDuplicate */
    private $emailDuplication;

    /** @var  Response */
    private $isEmailDuplicatedResponse;

    /**
     * @param TestSupportHelper $testSupportHelper
     * @param CustomerService $customerService
     * @param Session $session
     * @param Person $person
     * @param Tester $tester
     * @param Vts $vts
     * @param AuthorisedExaminer $authorisedExaminer
     * @param Notification $notification
     * @param EmailDuplicate $emailDuplication
     */
    public function __construct(
        TestSupportHelper $testSupportHelper,
        CustomerService $customerService,
        Session $session,
        Person $person,
        Tester $tester,
        Vts $vts,
        AuthorisedExaminer $authorisedExaminer,
        Notification $notification,
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        EmailDuplicate $emailDuplication
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->customerService = $customerService;
        $this->session = $session;
        $this->person = $person;
        $this->tester = $tester;
        $this->vts = $vts;
        $this->authorisedExaminer = $authorisedExaminer;
        $this->notification = $notification;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->emailDuplication = $emailDuplication;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->authorisedExaminerContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
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
            PHPUnit::assertEquals(AuthorisationForTestingMotStatusCode::QUALIFIED, $status, $class . ' not QLFD');
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

        $this->updateUserEmailResponse = $this->person->updateUserEmail(
            $this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId(), $this->newEmailAddress
        );
    }

    /**
     * @When /^I update a user's email address$/
     */
    public function iUpdateUsersEmailAddress()
    {
        $this->newEmailAddress = Random::getRandomEmail();

        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $this->updateUserEmailResponse = $this->person->updateUserEmail(
            $this->sessionContext->getCurrentAccessToken(), $this->getPersonUserId(), $this->newEmailAddress
        );
    }

    /**
     * @Then /^I will see my updated email address$/
     */
    public function iWillSeeMyUpdatedEmailAddress()
    {
        PHPUnit::assertSame(200, $this->updateUserEmailResponse->getStatusCode());
        PHPUnit::assertSame(
            $this->newEmailAddress,
            $this->updateUserEmailResponse->getBody()['data']['emails'][0]['email'],
            'Email address on User Profile is incorrect.'
        );
    }

    /**
     * @Then /^my email address will not be updated$/
     */
    public function myEmailAddressWillNotBeUpdated()
    {
        $body = $this->updateUserEmailResponse->getBody()->toArray();

        PHPUnit::assertSame(422, $this->updateUserEmailResponse->getStatusCode(), 'Did not receive 422 Unprocessable entity response');
        PHPUnit::assertFalse(isset($body['data']['emails']), 'Data key containing Email data was returned in response body.');
    }

    /**
     * @Then /^the user's email address will be updated$/
     */
    public function usersEmailAddressWillBeUpdated()
    {
        $body = $this->updateUserEmailResponse->getBody()->toArray();

        PHPUnit::assertSame(
            200,
            $this->updateUserEmailResponse->getStatusCode()
        );
        PHPUnit::assertSame(
            $this->newEmailAddress,
            $body['data']['emails'][0]['email'],
            'Email address on User Profile is incorrect.'
        );
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
            $this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId(), $email
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
                'email' => $row['email'],
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
        $this->testerDetailsResponse = $this->tester->getTesterDetails(
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

    /**
     * @When I add the role of :role to another user
     */
    public function iAddTheRoleOfToAnotherUser($role)
    {
        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $this->person->addPersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId(),
            $role
        );
    }

    /**
     * @When I add the role of :role to a :userRole
     */
    public function iAddTheRoleOfRoleToAUserRole($role, $userRole)
    {
        $userService = $this->testSupportHelper->userRoleServiceFactory($userRole);

        $this->personLoginData = $userService->create([]);

        $this->person->addPersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId(),
            $role

        );
    }

    /**
     *
     * @Given The user :userRole exists
     */
    public function theUserExists($userRole)
    {
        $userService = $this->testSupportHelper->userRoleServiceFactory($userRole);

        $this->personLoginData = $userService->create([]);
    }

    /**
     * @When The user has the role :role
     * @When I add the role of :role to the user
     */
    public function iAddTheRoleOfToA($role)
    {
        $this->person->addPersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId(),
            $role

        );
    }

    /**
     * @When I try to add the role of :role to myself
     */
    public function iAddTheRoleOfRoleToMyself($role)
    {
        $this->person->addPersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $role
        );
    }

    /**
     * @When I try to remove the role of :role from myself
     */
    public function iRemoveTheRoleOfRoleFromMyself($role)
    {
        $this->person->removePersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $role
        );
    }

    /**
     * @When I remove the role of :role from the user
     */
    public function iRemoveTheRoleOfRoleFromAUserRole($role)
    {
        $response = $this->person->removePersonRole(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId(),
            $role
        );

        PHPUnit::assertEquals(200, $response->getStatusCode(), "Unable to remove role '{$role}'");
    }

    /**
     * @When I change a user's group :group tester qualification status from :status to Qualified
     */
    public function iChangeAUsersGroupTesterQualificationStatusFromToQualified($group, $status)
    {
        $statusCode = $this->getAuthorisationForTestingMotStatusCode($status);

        $tester = $this->testSupportHelper->getTesterService();
        $this->personLoginData = $tester->create([
            'siteIds' => [1],
            "qualifications"=> [
                "A"=> $statusCode,
                "B"=> $statusCode
            ]
        ]);

        $this->testerQualificationResponse = $this->tester->updateTesterQualification(
            $this->sessionContext->getCurrentAccessToken(),
            $group,
            $this->getPersonUserId()
        );
    }

    private function getAuthorisationForTestingMotStatusCode($status)
    {
        switch ($status) {
            case "Unknown":
                $code = AuthorisationForTestingMotStatusCode::UNKNOWN;
                break;
            case "Initial Training Needed":
                $code = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;
                break;
            case "Demo Test Needed":
                $code = AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
                break;
            case "Qualified":
                $code = AuthorisationForTestingMotStatusCode::QUALIFIED;
                break;
            case "Refresher Needed":
                $code = AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED;
                break;
            case "Suspended":
                $code = AuthorisationForTestingMotStatusCode::SUSPENDED;
                break;
            default:
                throw new \InvalidArgumentException('Status \"' . $status . '\" not found');
        }

        return $code;
    }

    /**
     * @Then an error occurs
     */
    public function anErrorOccurs()
    {
        $errors = $this->testerQualificationResponse->getBody()->toArray()['errors'];
        PHPUnit::assertCount(1, $errors);
    }

    /**
     * @When I review my test logs
     */
    public function getTestLogs()
    {
        $this->userTestLogs = $this->tester->getTesterTestLogs(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
        $this->userTestLogsSummary = $this->tester->getTesterTestLogsSummary(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
    }

    /**
     * @Then /^([1-9]*) test logs should show today in summary section$/
     */
    public function TestLogsShouldShowTodayInSummarySection($number)
    {
        $summaryArray = $this->userTestLogsSummary->getBody()->toArray();

        PHPUnit::assertEquals($number, $summaryArray['data']['today']);
    }

    /**
     * @Then /^My test logs should return ([1-9]*) detailed records$/
     */
    public function MyTestLogsShouldReturnDetailedRecords($number)
    {
        $testLogsDataArray = $this->userTestLogs->getBody()->toArray();
        PHPUnit::assertEquals($number, $testLogsDataArray['data']['resultCount']);
    }

    /**
     * @Then the user's RBAC will have the role :role
     */
    public function theUserSRBACWillHaveTheRole($role)
    {
        $token = $this->getPersonToken();

        $rbacResponse = $this->person->getPersonRBAC(
            $token,
            $this->getPersonUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertTrue(in_array($role, $rolesAssigned), sprintf("Role %s has not been assigned", $role));
    }

    /**
     * @Then the user's RBAC will not have the role :role
     *
     */
    public function theUserSRBACWillNotHaveTheRole($role)
    {
        $token = $this->getPersonToken();

        $rbacResponse = $this->person->getPersonRBAC(
            $token,
            $this->getPersonUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];

        PHPUnit::assertFalse(in_array($role, $rolesAssigned), sprintf("Role %s has been assigned", $role));
    }

    /**
     * @Then my RBAC will still have the role :role
     * @Given my RBAC has the role :role
     */
    public function myRBACWillHaveTheRole($role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertTrue(in_array($role, $rolesAssigned), sprintf("Role %s has not been assigned", $role));
    }

    /**
     * @Then my RBAC will not contain the role :role
     */
    public function myRBACWillNotContainTheRole($role)
    {
        $rbacResponse = $this->person->getPersonRBAC(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );

        $rolesAssigned = $rbacResponse->getBody()->toArray();
        $rolesAssigned = $rolesAssigned['data']['normal']['roles'];
        PHPUnit::assertFalse(in_array($role, $rolesAssigned), sprintf("Role %s has been assigned", $role));
    }

    /**
     * @Given I have selected a user who needs to have a licence added to their profile
     */
    public function selectUserWithoutLicence()
    {
        $tester = $this->testSupportHelper->getTesterService();

        // duplicated from $this->createTester due to PHP 5.5 not supporting array constants
        $defaults = [
            'siteIds' => [1],
            "qualifications"=> [
                "A"=> $this->getAuthorisationForTestingMotStatusCode("Qualified"),
                "B"=> $this->getAuthorisationForTestingMotStatusCode("Qualified")
            ]
        ];

        $this->personLoginData = $tester->createWithoutLicence($defaults);
    }

    /**
     * @Given /^I have selected a user who needs to have their licence deleted|edited$/
     */
    public function selectUserWithLicence()
    {
        $this->createTester();
    }

    /**
     * @When I add a licence :licenceNumber to the user's profile
     * @When I update the licence to :licenceNumber
     * @var string $licenceNumber
     */
    public function updateUserLicence($licenceNumber)
    {
        $this->updateUserLicenceWithRegion($licenceNumber, \DvsaCommon\Enum\LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES);
    }

    /**
     * @When I add a licence :licenceNumber with the region :licenceRegion to the user's profile
     * @When I update the licence to :licenceNumber and the region :licenceRegion
     * @var string $licenceNumber
     * @var string $licenceRegion
     */
    public function updateUserLicenceWithRegion($licenceNumber, $licenceRegion)
    {
        $testerId = $this->personLoginData->data['personId'];

        // create JSON to send to endpoint
        $licenceDetails = '{"drivingLicenceNumber": "' . $licenceNumber . '", "drivingLicenceRegion": "';
        $licenceDetails .= $licenceRegion . '"}';

        $response = $this->customerService->updateLicence(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId,
            $licenceDetails
        );

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @Then their licence should match :licenceNumber
     * @var string $licenceNumber
     */
    public function licenceNumbersMatch($licenceNumber)
    {
        $testerId = $this->personLoginData->data['personId'];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals($licenceNumber, $testerDetails->getBody()['data']['drivingLicence']);
    }

    /**
     * @Then the user should not have a licence associated with their account
     */
    public function licenceDoesNotExist()
    {
        $testerId = $this->personLoginData->data['personId'];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals('', $testerDetails->getBody()['data']['drivingLicence']);
    }

    /**
     * @Then their licence should not match :licenceNumber
     */
    public function theirLicenceShouldNotMatch($licenceNumber)
    {
        $testerId = $this->personLoginData->data['personId'];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertNotEquals($licenceNumber, $testerDetails->getBody()['data']['drivingLicence']);
    }

    /**
     * @When I delete the user's licence
     */
    public function removeUserLicence()
    {
        $testerId = $this->personLoginData->data['personId'];

        $response = $this->customerService->deleteLicence(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return int
     */
    public function getPersonUserId()
    {
        if (!isset($this->personLoginData->data['personId'])) {
            throw new \BadMethodCallException('No person id exists');
        }

        return $this->personLoginData->data['personId'];
    }

    /**
     * @return string
     */
    public function getPersonUsername()
    {
        if (!isset($this->personLoginData->data['username'])) {
            throw new \BadMethodCallException('No person username exists');
        }
        return $this->personLoginData->data['username'];
    }

    /**
     * @return string
     */
    public function getPersonPassword()
    {
        if (!isset($this->personLoginData->data['password'])) {
            throw new \BadMethodCallException('No person password exists');
        }
        return $this->personLoginData->data['password'];
    }

    /**
     * @Given I nominate user to TESTER role
     */
    public function iNominateUserToTesterRole()
    {
        $this->createTester();
        $this->nominateToSiteRole("TESTER");
    }

    /**
     * @Given I nominate user to SITE-ADMIN role
     */
    public function iNominateUserToSiteAdminRole()
    {
        $siteIds = [$this->vtsContext->getSite()["id"]];
        $params = ["siteIds" => $siteIds];
        $this->createTester($params);

        $this->nominateToSiteRole("SITE-ADMIN");
    }

    /**
     * @Given I nominate user to SITE-MANAGER role
     */
    public function iNominateUserToSiteManagerRole()
    {
        $siteIds = [$this->vtsContext->getSite()["id"]];
        $params = ["siteIds" => $siteIds];
        $this->createTester($params);
        $this->nominateToSiteRole("SITE-MANAGER");
    }

    /**
     * @Given I nominate user to Authorised examiner delegate role
     */
    public function iNominateUserToAedRole()
    {
        $params = [
            "siteIds" => [1]
        ];
        $this->createTester($params);
        $this->nominateToOrganisationRole("Authorised examiner delegate");
    }

    /**
     * @Given I nominate user to Authorised examiner delegate manager role
     */
    public function iNominateUserToAedmRole()
    {
        $params = ["siteIds" => [1]];
        $this->createTester($params);
        $this->nominateToOrganisationRole("Authorised examiner designated manager");
    }

    public function createTester(array $params = [])
    {
        $defaults = [
            'siteIds' => [1],
            "qualifications"=> [
                "A"=> $this->getAuthorisationForTestingMotStatusCode("Qualified"),
                "B"=> $this->getAuthorisationForTestingMotStatusCode("Qualified")
            ]
        ];

        $params = array_replace($defaults, $params);

        $tester = $this->testSupportHelper->getTesterService();
        $this->personLoginData = $tester->create($params);

        return $this->personLoginData;
    }

    public function createAedm(array $data = [], $aeId = null)
    {
        if (is_null($aeId)) {
            $ae = $this->authorisedExaminerContext->createAE();
            $aeId = $ae["id"];
        }

        $defaults = [
            "aeIds" => [$aeId],
        ];

        $data = array_replace($defaults, $data);

        $aedm = $this->testSupportHelper->getAedmService();
        $this->personLoginData = $aedm->create($data);

        return $this->personLoginData;
    }

    public function createSiteAdmin($siteId)
    {
        $siteManagerService = $this->testSupportHelper->getSiteUserDataService();

        $data = [
            "siteIds" => [ $siteId ],
            "requestor" => ["username" => "", "password" => ""]
        ];

        $user = $siteManagerService->create($data, "SITE-ADMIN");
        return $this->session->startSession($user->data['username'], $user->data['password']);
    }

    private function nominateToSiteRole($role)
    {
        $siteId = $this->vtsContext->getSite()["id"];
        $token = $this->sessionContext->getCurrentAccessToken();
        $response = $this->vts->nominateToRole($this->getPersonUserId(), $role, $siteId, $token);
        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    private function nominateToOrganisationRole($role)
    {
        $aeId = $this->authorisedExaminerData->get()->getId();
        $token = $this->sessionContext->getCurrentAccessToken();
        $response = $this->authorisedExaminer->nominate($this->getPersonUserId(), $role, $aeId, $token);
        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    private function denominateFromOrganisationRole($role)
    {
        $token = $this->sessionContext->getCurrentAccessToken();
        $aeId = $this->authorisedExaminerContext->getAE()['id'];

        // Retrieve details from organisation
        $positions = $this->authorisedExaminer->getAuthorisedExaminerPositions($token, $aeId);
        $positionsToArray = $positions->getBody()->toArray();

        $positionId = null;

        foreach ($positionsToArray['data'] as $position) {
            if ($position['role'] == $role) {
                $positionId = $position['id'];
                continue;
            }
        }

        if (is_null($positionId)) {
            throw new \InvalidArgumentException('Invalid Position Id');
        }

        // Remove role from organisation using the current session from context
        $response = $this->authorisedExaminer->denominate($aeId, $positionId, $token);

        PHPUnit::assertEquals(200, $response->getStatusCode());
    }

    /**
     * @Then a user has new site role :role
     */
    public function aUserHasNewSiteRole($role)
    {
        $detailsResponse = $this->person->getPersonDetails(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId()
        );

        $siteId = $this->vtsContext->getSite()["id"];
        $roles = $detailsResponse->getBody()['data']['roles']->toArray();
        $siteRoles = $roles["sites"][$siteId]["roles"];

        PHPUnit::assertTrue(in_array($role, $siteRoles), "Site role '" . $role . "' not found");
    }

    /**
     * @Then a user has new organisation role :role
     */
    public function aUserHasNewOrganisationRole($role)
    {
        $detailsResponse = $this->person->getPersonDetails(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId()
        );

        $aeId = $this->authorisedExaminerData->get()->getId();
        $roles = $detailsResponse->getBody()['data']['roles']->toArray();

        $orgRoles = $roles["organisations"][$aeId]["roles"];

        PHPUnit::assertTrue(in_array($role, $orgRoles), "Organisation role '" . $role . "' not found");
    }

    /**
     * @When I have a Tester Qualification status of :status for group :group
     *
     * @param $status
     * @param $group
     */
    public function iHaveATesterQualificationStatusForGroup($status, $group)
    {
        $this->iGetMyProfileDetails();

        $testerService = $this->testSupportHelper->getTesterService();
        $statusCode = $this->getAuthorisationForTestingMotStatusCode($status);

        $testerService->updateTesterQualificationStatus($this->sessionContext->getCurrentUserId(), $group, $statusCode);
    }

    /**
     * Login as the person stored and returns the token for them
     * @return string
     * @throws Exception
     */
    public function getPersonToken()
    {
        $tokenResponse = $this->session->startSession(
            $this->getPersonUsername(),
            $this->getPersonPassword()
        );
        return $tokenResponse->getAccessToken();
    }

    /**
     * @Then I denominate user to Authorised examiner delegate role to new organisation
     */
    public function iDenominateUserToAuthorisedExaminerDelegateRoleToNewOrganisation()
    {
        $this->denominateFromOrganisationRole("Authorised examiner delegate");
    }

    /**
     * @Then I denominate user to Authorised examiner designated manager role to new organisation
     */
    public function iDenominateUserToAuthorisedExaminerDesignatedManagerRoleToNewOrganisation()
    {
        $this->denominateFromOrganisationRole("Authorised examiner designated manager");
    }

    /**
     * @Given I nominate user to Authorised Examiner Delegate role to new organisation
     */
    public function iNominateUserToAuthorisedExaminerDelegateRoleToNewOrganisation()
    {
        $this->authorisedExaminerContext->createAE();

        $params = ["siteIds" => [1]];
        $this->createTester($params);
        $this->nominateToOrganisationRole("Authorised examiner delegate");
    }

    /**
     * @Given I nominate user to Authorised Examiner Designated Manager role to new organisation
     */
    public function iNominateUserToAuthorisedExaminerDesignatedManagerRoleToNewOrganisation()
    {
        $this->authorisedExaminerContext->createAEwithoutAedm();
        $site = $this->siteData->create(["aeName" => AuthorisedExaminerData::DEFAULT_NAME]);
        $params = ["siteIds" => [$site->getId()]];
        $this->createTester($params);
        $this->nominateToOrganisationRole("Authorised examiner designated manager");
    }

    /**
     * @Then a user does not have organisation role :arg1
     */
    public function aUserDoesNotHaveOrganisationRole($role)
    {
        $token = $this->sessionContext->getCurrentAccessToken();
        $aeId = $this->authorisedExaminerContext->getAE()['id'];

        // Retrieve details from organisation
        $positions = $this->authorisedExaminer->getAuthorisedExaminerPositions($token, $aeId);
        $positionsToArray = $positions->getBody()->toArray();

        $positionId = null;

        foreach ($positionsToArray['data'] as $position) {
            if ($position['role'] == $role) {
                $positionId = $position['id'];
                continue;
            }
        }

        PHPUnit::assertNull($positionId);
    }

    /**
     * @When /^I change a person's name to (.*) (.*) (.*)$/
     *
     * @param $firstName
     * @param $middleName
     * @param $lastName
     */
    public function iChangeAPersonsName($firstName, $middleName, $lastName)
    {
        $this->newName = ['firstName' => $firstName,
                          'middleName' => $middleName,
                          'lastName' => $lastName];

        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateNameResponse = $this->person->changeName($token, $this->getPersonUserId(), $this->newName);
    }

    /**
     * @When I change my own name to :firstName :middleName :lastName
     *
     * @param $firstName
     * @param $middleName
     * @param $lastName
     */
    public function iChangeMyOwnName($firstName, $middleName, $lastName)
    {
        $this->newName = ['firstName' => $firstName,
                          'middleName' => $middleName,
                          'lastName' => $lastName];

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateNameResponse = $this->person->changeName($token, $this->sessionContext->getCurrentUserId(), $this->newName);
    }


    /**
     * @Then The person's name should be updated
     */
    public function thePersonsNameShouldBeUpdated()
    {
        PHPUnit::assertSame(200, $this->updateNameResponse->getStatusCode());
    }

    /**
     * @Then The person's name should not be updated
     */
    public function thePersonsNameShouldNotBeUpdated()
    {
        PHPUnit::assertSame(400, $this->updateNameResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateNameResponse->getBody()['errors']);
    }

    /**
     * @Then I am forbidden from changing name
     */
    public function iAmForbiddenFromChangingName()
    {
        PHPUnit::assertSame(403, $this->updateNameResponse->getStatusCode());
    }

    /**
     * @Then the person should receive a notification about the change
     */
    public function userGetsNotificationAboutChange()
    {
        $session = $this->session->startSession(
            $this->getPersonUsername(),
            $this->getPersonPassword()
        );

        $response = $this->notification->fetchNotificationForPerson($session->getAccessToken(), $session->getUserId());
        $notifications = $response->getBody()->toArray();

        PHPUnit::assertNotEmpty($notifications);

        // Assert that the notification that we are expecting exists
        $found = false;
        foreach ($notifications['data'] as $notification) {
            if ($notification['templateId'] == self::PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID) {
                $found = true;
                break;
            }
        }
        PHPUnit::assertTrue($found, 'Notification for personal details being changed not found');
    }

    /**
     * @Then the person should not receive a notification about the change
     */
    public function userDoesNotGetNotificationAboutChange()
    {
        $response = $this->notification->fetchNotificationForPerson(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
        $notifications = $response->getBody()->toArray();

        if (empty($notifications)) {
            PHPUnit::assertEmpty($notifications);
        } else {
            // Assert that there are no changed details notifications
            $found = false;
            foreach ($notifications['data'] as $notification) {
                if ($notification['templateId'] == self::PERSONAL_DETAILS_CHANGED_NOTIFICATION_ID) {
                    $found = true;
                    break;
                }
            }
            PHPUnit::assertFalse($found, 'Notification for personal details being changed was found');
        }
    }

    /**
     * @When /^I change a person's address to (.*), (.*), (.*), (.*), (.*), (.*)$/
     *
     * @param $firstLine
     * @param $secondLine
     * @param $thirdLine
     * @param $townOrCity
     * @param $country
     * @param $postcode
     */
    public function iChangeAPersonsAddress($firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        $this->newAddress = [
            'firstLine' => $firstLine,
            'secondLine' => $secondLine,
            'thirdLine' => $thirdLine,
            'townOrCity' => $townOrCity,
            'country' => $country,
            'postcode' => $postcode,
        ];

        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $otherUserId = $this->getPersonUserId();

        $token = $this->sessionContext->getCurrentAccessToken();

        $this->updateAddressResponse = $this->person->changeAddress($token, $otherUserId, $this->newAddress);
    }

    /**
     * @When /^I change my own address to (.*), (.*), (.*), (.*), (.*), (.*)$/
     *
     * @param $firstLine
     * @param $secondLine
     * @param $thirdLine
     * @param $townOrCity
     * @param $country
     * @param $postcode
     */
    public function iChangeMyOwnAddress($firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        $this->newAddress = [
            'firstLine' => $firstLine,
            'secondLine' => $secondLine,
            'thirdLine' => $thirdLine,
            'townOrCity' => $townOrCity,
            'country' => $country,
            'postcode' => $postcode,
        ];

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateAddressResponse = $this->person->changeAddress($token, $this->sessionContext->getCurrentUserId(), $this->newAddress);
    }

    /**
     * @Then The person's address is updated
     */
    public function thePersonsAddressIsUpdated()
    {
        PHPUnit::assertSame(200, $this->updateAddressResponse->getStatusCode());
        PHPUnit::assertSame($this->newAddress, $this->updateAddressResponse->getBody()['data']->toArray());
    }

    /**
     * @Then The person's address should not be updated
     */
    public function thePersonsAddressShouldNotBeUpdated()
    {
        PHPUnit::assertSame(400, $this->updateAddressResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateAddressResponse->getBody()['errors']);
    }

    /**
     * @Then I am forbidden from changing address
     */
    public function iAmForbiddenFromChangingAddress()
    {
        PHPUnit::assertSame(403, $this->updateAddressResponse->getStatusCode());
    }

    /**
     * @When /^I change a person date of birth to (.*) (.*) (.*)$/
     */
    public function iChangeAPersonDateOfBirthTo($day, $month, $year)
    {
        $this->newDateOfBirth = [
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ];
        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateDateOfBirthResponse = $this->person->changeDateOfBirth($token, $this->getPersonUserId(), $this->newDateOfBirth);

    }

    /**
     * @Then The person's date of birth should be updated
     */
    public function thePersonSDateOfBirthShouldBeUpdated()
    {
        PHPUnit::assertSame(200, $this->updateDateOfBirthResponse->getStatusCode());
    }
    /**
     *
     * @Then The person's date of birth should not be updated
     */
    public function thePersonSDateOfBirthShouldNotBeUpdated()
    {
        PHPUnit::assertSame(400, $this->updateDateOfBirthResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateDateOfBirthResponse->getBody()['errors']);
    }

    /**
     * @When I change my date of birth to :day-:month-:year
     */
    public function iChangeMyDateOfBirthTo($day, $month, $year)
    {
        $this->newDateOfBirth = [
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ];

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateDateOfBirthResponse = $this->person->changeDateOfBirth($token, $this->sessionContext->getCurrentUserId(), $this->newDateOfBirth);
    }

    /**
     * @When /^I change a person's telephone number to (.*)$/
     * @param $telephoneNumber
     */
    public function iChangeAPersonsTelephoneNumberTo($telephoneNumber)
    {
        $this->newTelephoneNumber = ['personTelephone' => $telephoneNumber];
        $userService = $this->testSupportHelper->getUserService();
        $this->personLoginData = $userService->create([]);

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateTelephoneNumberResponse = $this->person->changeTelephoneNumber($token, $this->getPersonUserId(), $this->newTelephoneNumber);
    }

    /**
     * @When /^I change my own telephone number to (.*)$/
     * @param $telephoneNumber
     */
    public function iChangeMyTelephoneNumberTo($telephoneNumber)
    {
        $this->newTelephoneNumber = ['personTelephone' => $telephoneNumber];

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateTelephoneNumberResponse = $this->person->changeTelephoneNumber($token, $this->sessionContext->getCurrentUserId(), $this->newTelephoneNumber);
    }

    /**
     * @Then /^the person's telephone number should be updated$/
     * @Then /^my telephone number should be updated$/
     */
    public function telephoneNumberShouldBeUpdated()
    {
        PHPUnit::assertSame(200, $this->updateTelephoneNumberResponse->getStatusCode());
    }

    /**
     * @Then /^the person's telephone number should not be updated$/
     * @Then /^my telephone number should not be updated$/
     */
    public function telephoneNumberShouldNotBeUpdated()
    {
        PHPUnit::assertSame(400, $this->updateTelephoneNumberResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateTelephoneNumberResponse->getBody()['errors']);
    }

    /**
     * @When I pass :testCount normal tests
     */
    public function iPassNormalTests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestContext->vehicleHasANormalTestTestStarted();
        }
    }

    /**
     * @When I fail :testCount normal tests
     */
    public function iFailNormalTests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestContext->vehicleHasMotTestFailed();
        }
    }

    /**
     * @When I perform :testCount retests
     */
    public function iPerformRetests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestContext->vehicleHasMotTestReTestStarted();
        }
    }

    /**
     * @When I perform :testCount demotests
     */
    public function iPerformDemotests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestContext->vehicleHasARoutineDemonstrationTestTestStarted();
        }
    }

    /**
     * @When I start and abort :testCount tests
     */
    public function iStartAndAbortTests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestContext->vehicleHasAbortedTest();
        }
    }

    /**
     * @When I get my person stats
     */
    public function iGetMyPersonalStats()
    {
        $this->personStats = $this->person->getPersonStats(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId()
        );
    }

    /**
     * @Then person stats show :conductedTests conducted tests :passedNormalTests passed tests and :failedNormalTests failed tests
     */
    public function personStatsShowCorrectTestCount($conductedTests, $passedNormalTests, $failedNormalTests)
    {
        $data = $this->personStats->getBody()["data"];

        PHPUnit::assertEquals($conductedTests, $data["total"]);
        PHPUnit::assertEquals($passedNormalTests, $data["numberOfPasses"]);
        PHPUnit::assertEquals($failedNormalTests, $data["numberOfFails"]);
    }

    /**
     * @Then /^the my profile details are returned$/
     */
    public function theMyProfileDetailsAreReturned()
    {
        $currentUsername = $this->sessionContext->getCurrentUser()->getUsername();
        $retrievedUsername = $this->testerDetailsResponse->getBody()->toArray()['data']['username'];
        PHPUnit::assertEquals($retrievedUsername, $currentUsername);
    }

    /**
     * @When /^I attempt to create a new AE$/
     */
    public function iAttemptToCreateANewAE()
    {
        $this->iGetMyProfileDetails();
        $username =  $this->testerDetailsResponse->getBody()['data']['username'];
        try {
            $this->authorisedExaminerContext->iAttemptsToCreateAEAs($username, "Password1");
            $this->ae = "CREATED";
        } catch (UnauthorisedException $ue) {
            $this->ae = "FORBIDDEN";
        }
    }

    /**
     * @Then /^the creation of AE will be (.*)$/
     */
    public function theCreationOfAEWillBe($expectedStatus)
    {
        PHPUnit::assertEquals($this->ae, $expectedStatus);
    }

    /**
     * @When /^I attempt to remove an AE$/
     */
    public function iAttemptToRemoveAnAE()
    {
        $this->authorisedExaminerContext->createAE();
        $this->ae = $this->authorisedExaminerContext->iAttemptToRemoveAnAuthorisedExaminer();
    }

    /**
     * @Then /^the removal of AE will be (.*)$/
     */
    public function theRemovalOfAEWillBe($expectedStatus)
    {
        try {
            $this->ae->getBody()['data'];
            $actualStatus = "REMOVED";
        } catch (Exception $error) {
            $actualStatus = $this->ae->getBody()['errors'][0]['message'];
        }

        PHPUnit::assertEquals($actualStatus, $expectedStatus);
    }

    /**
     * @Given I have :status status for group :group
     */
    public function iHaveStatusForGroup($status, $group)
    {
        $this->setQualificationStatus($this->sessionContext->getCurrentUserId(), $status, $group);
    }

    public function setQualificationStatus($personId, $status, $group)
    {
        if (!VehicleClassGroupCode::exists($group)) {
            throw new InvalidArgumentException("Group '" . $group . "' does not exist.");
        }

        if ($status === 'NOT_APPLIED') {
            $this->testSupportHelper->getTesterService()->removeTesterQualificationStatusForGroup($personId, $group);
            return;
        }

        $allowedStatuses = [
            'INITIAL_TRAINING_NEEDED' => AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED
        ];

        $code = ArrayUtils::tryGet($allowedStatuses, $status);

        $this->testSupportHelper->getTesterService()->insertTesterQualificationStatus($personId, $group, $code);
    }

    /**
     * @Given There is a tester :testerName associated with sites:
     */
    public function thereIsATesterAssociatedWithSites($testerName, Table $table)
    {
        $sites = [];
        $rows = $table->getColumnsHash();
        foreach ($rows  as $row) {
            $sites[] = $this->vtsContext->getSite($row["site"])["id"];
        }

        $tester = $this->createTester(["siteIds" => $sites])->data;
        $authenticatedUser = $this->session->startSession($tester["username"], $tester["password"]);

        $this->addUser($testerName, $authenticatedUser);

        return $authenticatedUser;
    }

    public function addUser($key, AuthenticatedUser $user, $overwrite = true)
    {
        if (array_key_exists($user->getUsername(), $this->users) && $overwrite === false) {
            throw new \InvalidArgumentException(sprintf("User with username '%s' already exist.", $user->getUsername()));
        }

        $this->users[$key] = $user;

        return $this;
    }

    /**
     * @param string $key
     * @return AuthenticatedUser
     */
    public function getUser($key)
    {
        if (array_key_exists($key, $this->users)) {
            return $this->users[$key];
        }

        throw new \InvalidArgumentException(sprintf("User '%s' does not exist.", $key));
    }

    /**
     * @Then the nominated user has a pending organisation role :role
     */
    public function theNominatedUserHasAPendingOrganisationRole($role)
    {
        $response = $this->person->getPendingRoles(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId()
        );

        $aeId = $this->authorisedExaminerContext->getAe()["id"];
        $roles = $response->getBody()['data']->toArray();

        $orgRoles = $roles["organisations"][$aeId]["roles"];

        PHPUnit::assertTrue(in_array($role, $orgRoles), "Organisation role '$role' should be pending");
    }

    /**
     * @Then the nominated user has a pending site role :role
     */
    public function theNominatedUserHasAPendingSiteRole($role)
    {
        $response = $this->person->getPendingRoles(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getPersonUserId()
        );

        $siteId = $this->vtsContext->getSite()["id"];
        $roles = $response->getBody()['data']->toArray();
        $siteRoles = $roles["sites"][$siteId]["roles"];

        PHPUnit::assertTrue(in_array($role, $siteRoles), "Site role '$role' should be pending");
    }

    /**
     * @When I update my email to one that is already in use.
     */
    public function iTryToUpdateMyEmailToAnAlreadyInUseEmail()
    {
        $this->createTester([
            'emailAddress' => 'testduplicated@emailserviceprovider.com',
        ]);

        $this->isEmailDuplicatedResponse = $this->emailDuplication->checkIsDuplicate(
            $this->sessionContext->getCurrentAccessToken(),
            'testduplicated@emailserviceprovider.com'
        );
    }

    /**
     * @Then I should receive an a response with true as the email is in use.
     */
    public function emailIsDuplicated()
    {
        PHPUnit::assertSame(true, $this->isEmailDuplicatedResponse->getBody()['data']['isDuplicate']);
    }

    /**
     * @When I update my email that is not already in use.
     */
    public function iTryToUpdateMyEmailToANewEmail()
    {
        $this->isEmailDuplicatedResponse = $this->emailDuplication->checkIsDuplicate(
            $this->sessionContext->getCurrentAccessToken(),
            'thisemailbetternotbeinuse@emailserviceprovider.com'
        );
    }

    /**
     * @When I update my email that is not already in use while not logged in.
     */
    public function iTryToUpdateMyEmailToANewEmailWhenNotLoggedIn()
    {
        $this->isEmailDuplicatedResponse = $this->emailDuplication->checkIsDuplicate(
            '',
            'thisemailbetternotbeinuse@emailserviceprovider.com'
        );
    }

    /**
     * @Then I should receive an a response with false as the email is not in use.
     */
    public function emailIsNotDuplicated()
    {
        PHPUnit::assertSame(false, $this->isEmailDuplicatedResponse->getBody()['data']['isDuplicate']);
    }
}
