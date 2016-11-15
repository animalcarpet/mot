<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Datasource\Random;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\EmailDuplicate;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class EmailContext implements Context
{
    private $person;
    private $emailDuplication;
    private $userData;
    private $siteData;

    private $newEmailAddress;
    /** @var Response */
    private $updateUserEmailResponse;

    public function __construct(
        Person $person,
        EmailDuplicate $emailDuplication,
        UserData $userData,
        SiteData $siteData
    )
    {
        $this->person = $person;
        $this->emailDuplication = $emailDuplication;
        $this->userData = $userData;
        $this->siteData = $siteData;
    }

    /**
     * @When /^I update my email address on my profile$/
     */
    public function iUpdateMyEmailAddressOnMyProfile()
    {
        $this->newEmailAddress = Random::getRandomEmail();

        $this->updateUserEmailResponse = $this->person->updateUserEmail(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId(),
            $this->newEmailAddress
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
     * @When /^I try update my email address to (.*)$/
     */
    public function iUpdateMyEmailAddressToAnInvalidAddress($email)
    {
        try {
            $this->updateUserEmailResponse = $this->person->updateUserEmail(
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->userData->getCurrentLoggedUser()->getUserId(),
                $email
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->updateUserEmailResponse = $this->person->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
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
     * @When I update :user email address
     */
    public function iUpdateUsersEmailAddress(AuthenticatedUser $user)
    {
        $this->newEmailAddress = Random::getRandomEmail();
        $this->updateUserEmailResponse = $this->person->updateUserEmail(
            $this->userData->getCurrentLoggedUser()->getAccessToken(), $user->getUserId(), $this->newEmailAddress
        );
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
     * @When I update my email to one that is already in use.
     */
    public function iTryToUpdateMyEmailToAnAlreadyInUseEmail()
    {
        $emailAddress = 'testduplicated@emailserviceprovider.com';
        $siteId = $this->siteData->get()->getId();

        $this->userData->createTesterWithParams(['emailAddress'=> $emailAddress, PersonParams::SITE_IDS => [$siteId]], "Jack Sparrow");

        $this->emailDuplication->checkIsDuplicate(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $emailAddress
        );
    }

    /**
     * @When I update my email that is not already in use.
     */
    public function iTryToUpdateMyEmailToANewEmail()
    {
        $this->emailDuplication->checkIsDuplicate(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            'thisemailbetternotbeinuse@emailserviceprovider.com'
        );
    }

    /**
     * @Then I should receive an a response with true as the email is in use.
     */
    public function emailIsDuplicated()
    {
        PHPUnit::assertSame(true, $this->emailDuplication->getLastResponse()->getBody()['data']['isDuplicate']);
    }

    /**
     * @Then I should receive an a response with false as the email is not in use.
     */
    public function emailIsNotDuplicated()
    {
        PHPUnit::assertSame(false, $this->emailDuplication->getLastResponse()->getBody()['data']['isDuplicate']);
    }

    /**
     * @When I update my email that is not already in use while not logged in.
     */
    public function iTryToUpdateMyEmailToANewEmailWhenNotLoggedIn()
    {
        $this->emailDuplication->checkIsDuplicate(
            '',
            'thisemailbetternotbeinuse@emailserviceprovider.com'
        );
    }
}