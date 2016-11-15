<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonTelephoneContext implements Context
{
    private $person;
    private $userData;

    public function __construct(Person $person, UserData $userData)
    {
        $this->person = $person;
        $this->userData = $userData;
    }

    /**
     * @When I change :user telephone number to :telephoneNumber
     */
    public function iChangeAPersonsTelephoneNumberTo(AuthenticatedUser $user, $telephoneNumber)
    {
        $this->person->changeTelephoneNumber(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            ['personTelephone' => $telephoneNumber]
        );
    }

    /**
     * @When I try change :user telephone number to :telephoneNumber
     */
    public function iTryChangeAPersonsTelephoneNumberTo(AuthenticatedUser $user, $telephoneNumber)
    {
        try {
            $this->iChangeAPersonsTelephoneNumberTo($user, $telephoneNumber);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then /^the person's telephone number should be updated$/
     * @Then /^my telephone number should be updated$/
     */
    public function telephoneNumberShouldBeUpdated()
    {
        $lastResponse = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $lastResponse->getStatusCode());
    }

    /**
     * @Then /^the person's telephone number should not be updated$/
     * @Then /^my telephone number should not be updated$/
     */
    public function telephoneNumberShouldNotBeUpdated()
    {
        $lastResponse = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $lastResponse->getStatusCode());
        PHPUnit::assertNotEmpty($lastResponse->getBody()->getErrors());
    }

    /**
     * @When I change my own telephone number to :telephoneNumber
     */
    public function iChangeMyTelephoneNumberTo($telephoneNumber)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->person->changeTelephoneNumber(
            $user->getAccessToken(),
            $user->getUserId(),
            ['personTelephone' => $telephoneNumber]
        );
    }

    /**
     * @When I try change my own telephone number to :telephoneNumber
     */
    public function iTryChangeMyTelephoneNumberTo($telephoneNumber)
    {
        try {
            $this->iChangeMyTelephoneNumberTo($telephoneNumber);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }
}
