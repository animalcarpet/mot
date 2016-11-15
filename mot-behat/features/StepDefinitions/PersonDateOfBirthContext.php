<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonDateOfBirthContext implements Context
{
    private $person;
    private $userData;

    public function __construct(Person $person, UserData $userData)
    {
        $this->person = $person;
        $this->userData = $userData;
    }

    /**
     * @When I change :user date of birth to :date
     */
    public function iChangeAPersonDateOfBirthTo(AuthenticatedUser $user, \DateTime $date)
    {
        $this->changeDateOfBirth(
            $this->userData->getCurrentLoggedUser(),
            $user,
            $date->format("d"),
            $date->format("m"),
            $date->format("Y")
        );
    }

    /**
     * @When I try change :user date of birth to :date
     */
    public function iTryChangeAPersonDateOfBirthTo(AuthenticatedUser $user, \DateTime $date)
    {
        try {
            $this->iChangeAPersonDateOfBirthTo($user, $date);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I try change :user date of birth to :day :month :year
     */
    public function iTryChangeAPersonDateOfBirthToDate(AuthenticatedUser $user, $day, $month, $year)
    {
        try {
            $this->changeDateOfBirth(
                $this->userData->getCurrentLoggedUser(),
                $user,
                $day,
                $month,
                $year
            );
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then The person's date of birth should be updated
     */
    public function thePersonSDateOfBirthShouldBeUpdated()
    {
        $response = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @When I change my own date of birth to :date
     */
    public function iChangeMyOwnDateOfBirthTo(\DateTime $date)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->changeDateOfBirth(
            $user,
            $user,
            $date->format("d"),
            $date->format("m"),
            $date->format("Y")
        );
    }

    /**
     * @When I try change my own date of birth to :date
     */
    public function iTryChangeMyOwnDateOfBirthTo(\DateTime $date)
    {
        try {
            $this->iChangeMyOwnDateOfBirthTo($date);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    private function changeDateOfBirth(AuthenticatedUser $requestor, AuthenticatedUser $user, $day, $month, $year)
    {
        $this->person->changeDateOfBirth(
            $requestor->getAccessToken(),
            $user->getUserId(),
            [
                'day' => $day,
                'month' => $month,
                'year' => $year,
            ]
        );
    }

    /**
     *
     * @Then The person's date of birth should not be updated
     */
    public function thePersonSDateOfBirthShouldNotBeUpdated()
    {
        $lastResponse = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $lastResponse->getStatusCode());
        PHPUnit::assertNotEmpty($lastResponse->getBody()->getErrors());
    }
}
