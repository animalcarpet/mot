<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PasswordContext implements Context
{
    /**
     * @var Person
     */
    private $person;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var String
     */
    private $passwordResetToken;


    private $data = [];

    public function __construct(
        Person $person,
        Session $session,
        TestSupportHelper $testSupportHelper
    )
    {
        $this->person = $person;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }


    /**
     * @When user fills in :oldPassword, :newPassword, :confirmNewPassword
     */
    public function userFillsIn($oldPassword, $newPassword, $confirmNewPassword)
    {
        $this->data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => $oldPassword,
            ChangePasswordInputFilter::FIELD_PASSWORD => $newPassword,
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => $confirmNewPassword
        ];

    }

    /**
     * @Then my password is updated
     */
    public function myPasswordIsUpdated()
    {
        $this->sessionContext->getCurrentAccessToken();
        $response = $this->person->changePassword(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserIdOrNull(),
            $this->data
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then I can log in with new password :password
     */
    public function iCanLogInWithNewPassword($password)
    {
        $username = $this->sessionContext->getCurrentUser()->getUsername();
        $resposne = $this->session->startSession($username, $password);

        PHPUnit::assertEquals($username, $resposne->getUsername());
    }

    /**
     * @Then my password is not updated
     */
    public function myPasswordIsNotUpdated()
    {
        $this->sessionContext->getCurrentAccessToken();
        $response = $this->person->changePassword(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserIdOrNull(),
            $this->data
        );

        $errors = $response->getBody()->getErrors();
        $isEmpty = empty($errors);

        PHPUnit::assertFalse($isEmpty);
    }

    /**
     * @Given /^I have clicked forgotten password and I enter the (.*)$/
     */
    public function iHaveClickedForgottenPasswordAndIEnterThe($userId)
    {
        $param = [
            "userId" => $userId
        ];

        $this->response = $this->person->generateToken(
            $this->sessionContext->getCurrentAccessTokenOrNull(),
            $param
        );
    }

    /**
     * @Then /^the result should be (.*) with (.*)$/
     */
    public function theResultShouldBeWith($expectedResult, $expectedErrorMessage)
    {
        $responseBody = $responseBody = $this->getMotResponse();

        if ($this->response->getStatusCode() == HttpResponse::STATUS_CODE_200){
            $actualResult = $responseBody['data']['success'];
        } else {
            $actualResult = false;
            PHPUnit::assertEquals($this->findErrorMessage($expectedErrorMessage, $responseBody), $expectedErrorMessage);
        }

        PHPUnit::assertEquals($actualResult, ($expectedResult == "true") ? true : false);
    }

    /**
     * @When /^I validate the token$/
     */
    public function iValidateTheToken()
    {
        $param = [
            "token" => $this->passwordResetToken
        ];

        $this->response = $this->person->validateToken(
            $this->sessionContext->getCurrentAccessTokenOrNull(),
            $this->passwordResetToken,
            $param
        );
    }

    /**
     * @Given /^I set remainder emails to be sent regarding password expiry$/
     */
    public function iSetRemainderEmailsToBeSentRegardingPasswordExpiry()
    {
        $this->response = $this->person->setEmailRemainderOfExpiryPassword(
            $this->sessionContext->getCurrentAccessToken(),
            ['expiry-date' => '10-02-2016']
        );
        PHPUnit::assertEquals($this->response->getStatusCode(), HttpResponse::STATUS_CODE_200);
    }

    /**
     * @Then /^the message should be (.*)$/
     */
    public function theMessageShouldBe($expectedMessage)
    {
        $responseBody = $this->getMotResponse();

        if ($this->response->getStatusCode() == HttpResponse::STATUS_CODE_200){
            $actualMessage = $responseBody['data']['type']['name'];
        } else {
            $actualMessage = $this->findErrorMessage($expectedMessage, $responseBody);
        }

        PHPUnit::assertEquals($actualMessage, $expectedMessage);
    }

    /**
     * @Given /^that I have a password reset token of type (.*)$/
     */
    public function thatIHaveAPasswordResetTokenOfType($tokenType)
    {
        switch ($tokenType) {
            case "valid":
                $this->iAttemptToResetMyPasswordWith($this->sessionContext->getCurrentUserIdOrNull());
                $this->passwordResetToken = $this->response->getBody()->getData()['token'];
                break;
            case "invalid":
                $this->passwordResetToken = "INVALIDTOKEN12";
                break;
            case "empty":
                $this->passwordResetToken = "";
                break;
            case "expired":
                $this->passwordResetToken = "";
                break;
            default:
                throw new InvalidArgumentException;
        }
    }

    /**
     * @Then /^the token should be (.*)$/
     */
    public function theTokenShouldBe($result)
    {
        if (array_key_exists('data', $this->response->getBody()->toArray())) {
            $actualResult = "valid";
        } else if (array_key_exists('errors', $this->response->getBody()->toArray())){
            $actualResult = "invalid";
        } else {
            $actualResult = "Request Failed";
        }
        PHPUnit::assertEquals($result, $actualResult);
    }

    /**
     * @Given /^I attempt to change my password to (.*)$/
     */
    public function iAttemptToChangeMyPasswordTo($newPassword)
    {
        $param = [
            'token' => $this->passwordResetToken,
            'newPassword' => $newPassword
        ];

        $this->response = $this->person->changePasswordWithToken(
            $this->sessionContext->getCurrentAccessTokenOrNull(),
            $param
        );
    }

    /**
     * @When /^I attempt to reset my password with (.*)$/
     */
    public function iAttemptToResetMyPasswordWith($userId)
    {
        if ($userId == "validUserId") {
            $userId = $this->sessionContext->getCurrentUserIdOrNull();
        } else if ($userId == "invalidUserId") {
            $userId = "0";
        }

        $param = [
            "userId" => $userId
        ];

        $this->response = $this->person->generateToken(
            $this->sessionContext->getCurrentAccessTokenOrNull(),
            $param
        );
    }

    private function getMotResponse(){
        if ($this->response instanceof Response) {
            return $this->response->getBody();
        } else {
            PHPUnit::assertTrue(true, "Test Failed"); return [];
        }
    }

    private function findErrorMessage($expectedMessage, $responseBody)
    {
        $actualMessage = "Message not found";
        for ($i=0; $i < count($responseBody['errors']); $i++){
            if ($responseBody['errors'][$i]['message'] == $expectedMessage) {
                return $expectedMessage;
            }
        }
        return $actualMessage;
    }
}
