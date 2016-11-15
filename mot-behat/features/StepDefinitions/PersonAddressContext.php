<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonAddressContext implements Context
{
    private $person;
    private $userData;

    private $newAddress = [];

    public function __construct(Person $person, UserData $userData)
    {
        $this->person = $person;
        $this->userData = $userData;
    }

    /**
     * @When I change :user address to :firstLine, :secondLine, :thirdLine, :townOrCity, :country, :postcode
     */
    public function iChangeAPersonsAddress(AuthenticatedUser $user, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        $this->newAddress = [
            'firstLine' => $firstLine,
            'secondLine' => $secondLine,
            'thirdLine' => $thirdLine,
            'townOrCity' => $townOrCity,
            'country' => $country,
            'postcode' => $postcode,
        ];

        $this->person->changeAddress(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $user->getUserId(),
            $this->newAddress
        );
    }

    /**
     * @When I try change :user address to :firstLine, :secondLine, :thirdLine, :townOrCity, :country, :postcode
     */
    public function iTryChangeAPersonsAddress(AuthenticatedUser $user, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        try {
            $this->iChangeAPersonsAddress($user, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then The person's address is updated
     */
    public function thePersonsAddressIsUpdated()
    {
        $response = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
        PHPUnit::assertSame($this->newAddress, $response->getBody()->getData());
    }

    /**
     * @When I change my own address to :firstLine, :secondLine, :thirdLine, :townOrCity, :country, :postcode
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

        $user = $this->userData->getCurrentLoggedUser();
        $this->updateAddressResponse = $this->person->changeAddress(
            $user->getAccessToken(),
            $user->getUserId(),
            $this->newAddress)
        ;
    }

    /**
     * @When I try change my own address to :firstLine, :secondLine, :thirdLine, :townOrCity, :country, :postcode
     */
    public function iTryChangeMyOwnAddress($firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        try {
            $this->iChangeMyOwnAddress($firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then I am forbidden from changing address
     */
    public function iAmForbiddenFromChangingAddress()
    {
        $response = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $response->getStatusCode());
    }

    /**
     * @Then The person's address should not be updated
     */
    public function thePersonsAddressShouldNotBeUpdated()
    {
        $response = $this->person->getLastResponse();
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
        PHPUnit::assertNotEmpty($response->getBody()->getErrors());
    }
}
