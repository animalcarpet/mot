<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\UserData;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
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
     * @var UserData
     */
    private $userData;

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
        UserData $userData
    )
    {
        $this->person = $person;
        $this->session = $session;
        $this->userData = $userData;
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
        $response = $this->person->changePassword(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId(),
            $this->data
        );

        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then I can log in with new password :password
     */
    public function iCanLogInWithNewPassword($password)
    {
        $username = $this->userData->getCurrentLoggedUser()->getUsername();
        $resposne = $this->session->startSession($username, $password);

        PHPUnit::assertEquals($username, $resposne->getUsername());
    }

    /**
     * @Then my password is not updated
     */
    public function myPasswordIsNotUpdated()
    {
        try {
            $response = $this->person->changePassword(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->userData->getCurrentLoggedUser()->getUserId(),
                $this->data
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $response = $this->person->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");

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
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
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
     * @When /^I validate the valid token$/
     */
    public function iValidateTheValidToken()
    {
        $param = [
            "token" => $this->passwordResetToken
        ];

        $this->response = $this->person->validateToken(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->passwordResetToken,
            $param
        );
    }

    /**
     * @When /^I validate the invalid token$/
     */
    public function iValidateTheInvalidToken()
    {
        try {
            $this->iValidateTheValidToken();
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->response = $this->person->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Given /^I set remainder emails to be sent regarding password expiry$/
     */
    public function iSetRemainderEmailsToBeSentRegardingPasswordExpiry()
    {
        $this->response = $this->person->setEmailRemainderOfExpiryPassword(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
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
     * @Then /^the response should contain user email address details/
     */
    public function theResponseShouldContainUserEmailAddressDetails()
    {
        $responseBody = $this->getMotResponse();

        if ($this->response->getStatusCode() == HttpResponse::STATUS_CODE_200){
            $actualEmailAddress = $responseBody['data']['person']['contactDetails'][0]['emails'][0]['email'];
        } else {
            $actualEmailAddress = 'HTTP response code was not ' . HttpResponse::STATUS_CODE_200;
        }
        $patternForSuccessEmailAddress = '/^success[\S]+@[\S]+$/';
        $match = preg_match($patternForSuccessEmailAddress, $actualEmailAddress);

        PHPUnit::assertEquals(1, $match);
    }

    /**
     * @Given /^that I have a password reset token of type (.*)$/
     */
    public function thatIHaveAPasswordResetTokenOfType($tokenType)
    {
        switch ($tokenType) {
            case "valid":
                $this->iResetMyPasswordWith($this->userData->getCurrentLoggedUser()->getUserId());
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
     * @Given /^I change my password to (.*)$/
     */
    public function iChangeMyPasswordTo($newPassword)
    {
        $param = [
            'token' => $this->passwordResetToken,
            'newPassword' => $newPassword
        ];

        $this->response = $this->person->changePasswordWithToken(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $param
        );
    }

    /**
     * @Given /^I try to change my password to (.*)$/
     */
    public function iTryToChangeMyPasswordTo($newPassword)
    {
        try {
            $this->iChangeMyPasswordTo($newPassword);
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->response = $this->person->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When /^I reset my password with (.*)$/
     */
    public function iResetMyPasswordWith($userId)
    {
        if ($userId == "validUserId") {
            $userId = $this->userData->getCurrentLoggedUser()->getUserId();
        } else if ($userId == "invalidUserId") {
            $userId = "0";
        }

        $param = [
            "userId" => $userId
        ];

        $this->response = $this->person->generateToken(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $param
        );
    }

    /**
     * @When /^I try to reset my password with (.*)$/
     */
    public function iTryResetMyPasswordWith($userId)
    {
        try {
            $this->iResetMyPasswordWith($userId);
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->response = $this->person->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
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
