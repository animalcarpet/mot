<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Response;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\AccountRegistration;
use Dvsa\Mot\Behat\Support\Api\Session;
use PHPUnit_Framework_TestCase as PHPUnit;

class AccountRegistrationContext implements Context
{
    // This is not a real security question but constant is here to help context
    const SECURITY_QUESTION_ID_NAME_OF_DOG = 1;
    const SECURITY_QUESTION_ID_FIRST_KISS = 2;
    const PASSWORD = 'Password1';

    /**
     * @var array;
     */
    private $registrationData = [];

    /**
     * @var AccountRegistration
     */
    private $accountRegistration;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Dvsa\Mot\Behat\Support\Response
     */
    private $response;

    /**
     * @var string
     */
    private $username;

    /**
     * @param AccountRegistration $accountRegistration
     */
    public function __construct(
        AccountRegistration $accountRegistration,
        Session $session
    )
    {
        $this->accountRegistration = $accountRegistration;
        $this->session = $session;
    }

    /**
     * @Given For the :stepName step I input:
     */
    public function forTheNameStepIInput($stepName, TableNode $table)
    {
        $hash = $table->getColumnsHash();
        $this->setRegistrationData($stepName, $hash[0]);
    }

    /**
     * @Given I supply valid answers to the security questions
     */
    public function iSupplyValidAnswersToTheSecurityQuestions()
    {
        // The API expects question to be an id and not the actual question
        $this->setRegistrationData('securityQuestionFirst', [
            'question1' => self::SECURITY_QUESTION_ID_NAME_OF_DOG,
            'answer1' => 'Spot'
        ]);
        $this->setRegistrationData('securityQuestionSecond', [
            'question2' => self::SECURITY_QUESTION_ID_NAME_OF_DOG,
            'answer2' => 'Fred Flintstone'
        ]);
    }

    /**
     * @Given I provide a valid password
     */
    public function iProvideAValidPassword()
    {
        $this->setRegistrationData('password', [
            'password' => self::PASSWORD,
            'passwordConfirm' => self::PASSWORD
        ]);
    }

    /**
     * @When I confirm my details
     * @When I try to confirm my details
     * @When I try to register an account with the same email
     */
    public function iConfirmMyDetails()
    {
        $this->setRegistrationResponse($this->accountRegistration->registerUser($this->registrationData));
    }

    /**
     * @Then an account is created
     */
    public function anAccountIsCreated()
    {
        // look for a created response
        PHPUnit::assertEquals(200, $this->response->getStatusCode());
        $data = $this->getBodyData();
        PHPUnit::assertArrayHasKey('registeredPerson', $data);
        PHPUnit::assertNotEmpty($data['registeredPerson']);
        PHPUnit::assertArrayHasKey('id', $data['registeredPerson']);
        PHPUnit::assertNotEmpty($data['registeredPerson']['id']);
    }

    /**
     * @Then I will be able to login
     */
    public function iWillBeAbleToLogin()
    {
        $session = $this->session->startSession($this->getUserName(), self::PASSWORD);
        PHPUnit::assertNotNull($session);
    }

    /**
     * @Then an account is not created
     */
    public function anAccountIsNotCreated()
    {
        //check that errors are returned
        PHPUnit::assertEquals(422, $this->response->getStatusCode());
        $body = $this->response->getBody();
        PHPUnit::assertArrayHasKey('errors', $body);
        PHPUnit::assertNotEmpty($body['errors']);
    }

    /**
     * Add the data being set by the registration process into the class
     * @param string $stepName
     * @param array $keyValuePair
     */
    private function setRegistrationData($stepName, array $keyValuePair)
    {
        $stepName = 'step'.ucfirst($stepName);
        $this->registrationData[$stepName] = $keyValuePair;
    }

    private function setRegistrationResponse(Response $response)
    {
        $this->response = $response;
    }

    private function getRegisteredPerson()
    {
        $data = $this->getBodyData();
        return $this->returnHasKeyAndContent('registeredPerson', $data);
    }

    private function getUserName()
    {
        $person = $this->getRegisteredPerson();
        return $this->returnHasKeyAndContent('username', $person);
    }

    private function getBodyData()
    {
        $body = $this->response->getBody();
        return $this->returnHasKeyAndContent('data', $body);
    }

    private function returnHasKeyAndContent($needle, $haystack)
    {
        PHPUnit::assertArrayHasKey($needle, $haystack);
        PHPUnit::assertNotEmpty($haystack[$needle]);
        return $haystack[$needle];
    }

}