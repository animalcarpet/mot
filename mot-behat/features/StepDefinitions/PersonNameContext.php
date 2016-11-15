<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonNameContext implements Context
{
    private $person;
    private $userData;

    public function __construct(Person $person, UserData $userData)
    {
        $this->person = $person;
        $this->userData = $userData;
    }

    /**
     * @When I change :user name to :firstName :middleName :lastName
     */
    public function iChangeAPersonsName(AuthenticatedUser $user, $firstName, $middleName, $lastName)
    {
        $newName = [
            PersonParams::FIRST_NAME => $firstName,
            PersonParams::MIDDLE_NAME => $middleName,
            PersonParams::LAST_NAME => $lastName
        ];

        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->person->changeName($token, $user->getUserId(), $newName);
    }

    /**
     * @When I try change :user name to :firstName :middleName :lastName
     */
    public function iTryChangeAPersonsName(AuthenticatedUser $user, $firstName, $middleName, $lastName)
    {
        try {
            $this->iChangeAPersonsName($user, $firstName, $middleName, $lastName);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then The person's name should be updated
     */
    public function thePersonsNameShouldBeUpdated()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $this->person->getLastResponse()->getStatusCode());
    }

    /**
     * @When I change my own name to :firstName :middleName :lastName
     */
    public function iChangeMyOwnName($firstName, $middleName, $lastName)
    {
        $newName = [
            PersonParams::FIRST_NAME => $firstName,
            PersonParams::MIDDLE_NAME => $middleName,
            PersonParams::LAST_NAME => $lastName
        ];

        $user = $this->userData->getCurrentLoggedUser();
        $this->updateNameResponse = $this->person->changeName(
            $user->getAccessToken(),
            $user->getUserId(),
            $newName
        );
    }

    /**
     * @When I try change my own name to :firstName :middleName :lastName
     */
    public function iTryChangeMyOwnName($firstName, $middleName, $lastName)
    {
        try {
            $this->iChangeMyOwnName($firstName, $middleName, $lastName);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then I am forbidden from changing name
     */
    public function iAmForbiddenFromChangingName()
    {
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $this->person->getLastResponse()->getStatusCode());
    }

    /**
     * @Then The person's name should not be updated
     */
    public function thePersonsNameShouldNotBeUpdated()
    {
        $response = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
        PHPUnit::assertNotEmpty($response->getBody()->getErrors());
    }
}
