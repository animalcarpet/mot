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
use DvsaCommon\Enum\RoleCode;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\Dto\Site\SiteDto;
use Zend\Http\Response as HttpResponse;
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

    private $userData;

    private $motTestData;

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
        EmailDuplicate $emailDuplication,
        UserData $userData,
        MotTestData $motTestData
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
        $this->userData = $userData;
        $this->motTestData = $motTestData;
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
        $classes = $this->personalMotTestingClasses->getBody()->getData();
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
            HttpResponse::STATUS_CODE_200,
            $this->personalDashboard->getStatusCode(),
            'Non-200 status code returned for dashboard'
        );

        // Check that the hero key in response data is 'tester'
        PHPUnit::assertEquals(
            'tester',
            $this->personalDashboard->getBody()->getData()['hero'],
            'Dashboard does not contain Tester'
        );
    }

    /**
     * @Then /^my Dashboard will return the MotTestNumber$/
     */
    public function myDashboardWillReturnTheMotTestNumber()
    {
        $inProgressTestNumber = $this->personalDashboard->getBody()->getData()['inProgressTestNumber'];
        $createdMotTestNumber = $this->motTestData->getLast()->getMotTestNumber();
        $message = 'In progress MOT test number %s does not match created MOT test number %s';

        PHPUnit::assertEquals(
            $inProgressTestNumber,
            $createdMotTestNumber,
            sprintf($message, $inProgressTestNumber, $createdMotTestNumber)
        );
    }

    /**
     * @Then /^my Dashboard should show I have a non-MOT Test in progress$/
     */
    public function myDashboardShouldShowIHaveANonMotTestInProgress()
    {
        PHPUnit::assertTrue(is_numeric($this->motTestContext->getMotTestNumber()), 'MOT test number is not numeric');

//        $inProgressTestNumber = $this->personalDashboard->getBody()['data']['inProgressTestNumber'];
//        $createdMotTestNumber = $this->motTestContext->getMotTestNumber();
//        $message = 'In progress MOT test number %s does not match created MOT test number %s';
//
//        PHPUnit::assertEquals(
//            $inProgressTestNumber,
//            $createdMotTestNumber,
//            sprintf($message, $inProgressTestNumber, $createdMotTestNumber)
//        );
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
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->updateUserEmailResponse->getStatusCode());
        PHPUnit::assertSame(
            $this->newEmailAddress,
            $this->updateUserEmailResponse->getBody()->getData()['emails'][0][PersonParams::EMAIL],
            'Email address on User Profile is incorrect.'
        );
    }

    /**
     * @Then /^my email address will not be updated$/
     */
    public function myEmailAddressWillNotBeUpdated()
    {
        $body = $this->updateUserEmailResponse->getBody()->toArray();

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_422, $this->updateUserEmailResponse->getStatusCode(), 'Did not receive 422 Unprocessable entity response');
        PHPUnit::assertFalse(isset($body['data']['emails']), 'Data key containing Email data was returned in response body.');
    }

    /**
     * @Then /^the user's email address will be updated$/
     */
    public function usersEmailAddressWillBeUpdated()
    {
        $body = $this->updateUserEmailResponse->getBody()->toArray();

        PHPUnit::assertSame(
            HttpResponse::STATUS_CODE_200,
            $this->updateUserEmailResponse->getStatusCode()
        );
        PHPUnit::assertSame(
            $this->newEmailAddress,
            $body['data']['emails'][0][PersonParams::EMAIL],
            'Email address on User Profile is incorrect.'
        );
    }

    /**
     * @Given /^I should receive an email mismatch message in the response$/
     */
    public function iShouldReceiveAnEmailMismatchMessageInTheResponse()
    {
        $expected = 'Email confirmation does not match the email provided';

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $this->updateUserEmailResponse->getStatusCode(), 'Did not receive 400 Bad Request response');
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
                PersonParams::USER_NAME => $row[PersonParams::USER_NAME],
                PersonParams::FIRST_NAME => $row[PersonParams::FIRST_NAME],
                PersonParams::LAST_NAME => $row[PersonParams::LAST_NAME],
                PersonParams::POST_CODE => $row[PersonParams::POST_CODE],
                PersonParams::DATE_OF_BIRTH => $row[PersonParams::DATE_OF_BIRTH],
                PersonParams::EMAIL => $row[PersonParams::EMAIL],
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
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $this->userHelpDeskData->getStatusCode(), 'No Search Results Returned, HTTP200 status code not returned in response');
        PHPUnit::assertEquals(Authentication::UNCLAIMED_ACCOUNT, $this->userHelpDeskData->getBody()['data']['userName'], 'Username in User Profile is incorrect');
    }

    /**
     * @Then /^the Users data will not be returned$/
     */
    public function noUserDataWillBeReturned()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_404, $this->userHelpDeskData->getStatusCode(), 'User data returned in ');
        PHPUnit::assertEquals('Person '.$this->userId.' not found not found', $this->userHelpDeskData->getBody()->getErrors()[0]['message'], 'Error Message');
    }

    /**
     * @When /^I get my Profile details$/
     */
    public function iGetMyProfileDetails()
    {
        $this->testerDetailsResponse = $this->tester->getTesterDetails(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );
    }

    /**
     * @Then /^I will see my username in my Profile$/
     */
    public function iWillSeeMyProfileDetails()
    {
        PHPUnit::assertEquals(
            $this->sessionContext->getCurrentUser()->getUsername(),
            $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME],
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
            $this->testerDetailsResponse->getBody()->getData()[PersonParams::ID],
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
        $roles = $this->testerDetailsResponse->getBody()->getData()['roles'];

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

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode(), "Unable to remove role '{$role}'");
    }

    /**
     * @When I change a user's group :group tester qualification status from :status to Qualified
     */
    public function iChangeAUsersGroupTesterQualificationStatusFromToQualified($group, $status)
    {
        $statusCode = $this->getAuthorisationForTestingMotStatusCode($status);

        $tester = $this->testSupportHelper->getTesterService();
        $this->personLoginData = $tester->create([
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
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
        $errors = $this->testerQualificationResponse->getBody()->getErrors();
        PHPUnit::assertCount(1, $errors);
    }

    /**
     * @When I review my test logs
     */
    public function getTestLogs()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->userTestLogs = $this->tester->getTesterTestLogs(
            $user->getAccessToken(),
            $user->getUserId()
        );
        $this->userTestLogsSummary = $this->tester->getTesterTestLogsSummary(
            $user->getAccessToken(),
            $user->getUserId()
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
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
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
        $testerId = $this->personLoginData->data[PersonParams::PERSON_ID];

        $params = [
            "drivingLicenceNumber" => $licenceNumber,
            "drivingLicenceRegion" => $licenceRegion
        ];

        $response = $this->customerService->updateLicence(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId,
            $params
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then their licence should match :licenceNumber
     * @var string $licenceNumber
     */
    public function licenceNumbersMatch($licenceNumber)
    {
        $testerId = $this->personLoginData->data[PersonParams::PERSON_ID];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals($licenceNumber, $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @Then the user should not have a licence associated with their account
     */
    public function licenceDoesNotExist()
    {
        $testerId = $this->personLoginData->data[PersonParams::PERSON_ID];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals('', $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @Then their licence should not match :licenceNumber
     */
    public function theirLicenceShouldNotMatch($licenceNumber)
    {
        $testerId = $this->personLoginData->data[PersonParams::PERSON_ID];

        $testerDetails = $this->customerService->helpDeskProfile(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertNotEquals($licenceNumber, $testerDetails->getBody()->getData()[PersonParams::DRIVING_LICENCE]);
    }

    /**
     * @When I delete the user's licence
     */
    public function removeUserLicence()
    {
        $testerId = $this->personLoginData->data[PersonParams::PERSON_ID];

        $response = $this->customerService->deleteLicence(
            $this->sessionContext->getCurrentAccessToken(),
            $testerId
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @return int
     */
    public function getPersonUserId()
    {
        if (!isset($this->personLoginData->data[PersonParams::PERSON_ID])) {
            throw new \BadMethodCallException('No person id exists');
        }

        return $this->personLoginData->data[PersonParams::PERSON_ID];
    }

    /**
     * @return string
     */
    public function getPersonUsername()
    {
        if (!isset($this->personLoginData->data[PersonParams::USERNAME])) {
            throw new \BadMethodCallException('No person username exists');
        }
        return $this->personLoginData->data[PersonParams::USERNAME];
    }

    /**
     * @return string
     */
    public function getPersonPassword()
    {
        if (!isset($this->personLoginData->data[PersonParams::PASSWORD])) {
            throw new \BadMethodCallException('No person password exists');
        }
        return $this->personLoginData->data[PersonParams::PASSWORD];
    }





    public function createTester(array $params = [])
    {
        $defaults = [
            PersonParams::SITE_IDS => [$this->siteData->get()->getId()],
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

        $testerService->updateTesterQualificationStatus($this->userData->getCurrentLoggedUser()->getUserId(), $group, $statusCode);
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
     * @When /^I change a person's name to (.*) (.*) (.*)$/
     *
     * @param $firstName
     * @param $middleName
     * @param $lastName
     */
    public function iChangeAPersonsName($firstName, $middleName, $lastName)
    {
        $this->newName = [
            PersonParams::FIRST_NAME => $firstName,
            PersonParams::MIDDLE_NAME => $middleName,
            PersonParams::LAST_NAME => $lastName
        ];

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
        $this->newName = [
            PersonParams::FIRST_NAME => $firstName,
            PersonParams::MIDDLE_NAME => $middleName,
            PersonParams::LAST_NAME => $lastName
        ];

        $token = $this->sessionContext->getCurrentAccessToken();
        $this->updateNameResponse = $this->person->changeName($token, $this->sessionContext->getCurrentUserId(), $this->newName);
    }


    /**
     * @Then The person's name should be updated
     */
    public function thePersonsNameShouldBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->updateNameResponse->getStatusCode());
    }

    /**
     * @Then The person's name should not be updated
     */
    public function thePersonsNameShouldNotBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $this->updateNameResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateNameResponse->getBody()->getErrors());
    }

    /**
     * @Then I am forbidden from changing name
     */
    public function iAmForbiddenFromChangingName()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $this->updateNameResponse->getStatusCode());
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
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->updateAddressResponse->getStatusCode());
        PHPUnit::assertSame($this->newAddress, $this->updateAddressResponse->getBody()->getData());
    }

    /**
     * @Then The person's address should not be updated
     */
    public function thePersonsAddressShouldNotBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $this->updateAddressResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateAddressResponse->getBody()->getErrors());
    }

    /**
     * @Then I am forbidden from changing address
     */
    public function iAmForbiddenFromChangingAddress()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $this->updateAddressResponse->getStatusCode());
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
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->updateDateOfBirthResponse->getStatusCode());
    }
    /**
     *
     * @Then The person's date of birth should not be updated
     */
    public function thePersonSDateOfBirthShouldNotBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $this->updateDateOfBirthResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateDateOfBirthResponse->getBody()->getErrors());
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
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->updateTelephoneNumberResponse->getStatusCode());
    }

    /**
     * @Then /^the person's telephone number should not be updated$/
     * @Then /^my telephone number should not be updated$/
     */
    public function telephoneNumberShouldNotBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $this->updateTelephoneNumberResponse->getStatusCode());
        PHPUnit::assertNotEmpty($this->updateTelephoneNumberResponse->getBody()->getErrors());
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
        $user = $this->userData->getCurrentLoggedUser();
        $this->personStats = $this->person->getPersonStats(
            $user->getAccessToken(),
            $user->getUserId()
        );
    }

    /**
     * @Then person stats show :conductedTests conducted tests :passedNormalTests passed tests and :failedNormalTests failed tests
     */
    public function personStatsShowCorrectTestCount($conductedTests, $passedNormalTests, $failedNormalTests)
    {
        $data = $this->personStats->getBody()->getData();

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
        $retrievedUsername = $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME];
        PHPUnit::assertEquals($retrievedUsername, $currentUsername);
    }

    /**
     * @When /^I attempt to create a new AE$/
     */
    public function iAttemptToCreateANewAE()
    {
        $this->iGetMyProfileDetails();
        $username =  $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME];
        $user = $this->userData->get($username);
        try {
            $this->authorisedExaminerData->createByUser($user, "Organisation Ltd");
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
     * @Then the nominated user :user has a pending organisation role :role
     */
    public function theNominatedUserHasAPendingOrganisationRole(AuthenticatedUser $user, $role)
    {
        $response = $this->person->getPendingRoles(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId()
        );

        $aeId = $this->authorisedExaminerData->get()->getId();
        $roles = $response->getBody()->getData();

        $orgRoles = $roles["organisations"][$aeId]["roles"];

        PHPUnit::assertTrue(in_array($role, $orgRoles), "Organisation role '$role' should be pending");
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
