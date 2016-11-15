<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Rest;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class RestContext implements Context
{
    /**
     * @var Rest
     */
    private $rest;

    /**
     * @var string|null
     */
    private $accessToken;

    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * @param Rest $rest
     */
    public function __construct(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * @Given I am not logged in
     */
    public function iAmNotLoggedIn()
    {
        $this->accessToken = null;
    }

    /**
     * @When I make a :method request to :endpoint
     */
    public function iMakeARequestTo($method, $endpoint)
    {
        try {
            $this->lastResponse = $this->rest->makeRequest($this->accessToken, $method, $endpoint);
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->lastResponse = $this->rest->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then I should receive an Unauthorised response
     */
    public function iShouldReceiveAnUnauthorisedResponse()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_401, $this->lastResponse->getStatusCode(), 'Did not receive 401 Unauthorised response');
    }

    /**
     * @Given I should not see any data in the response body
     */
    public function iShouldNotSeeAnyDataInTheResponseBody()
    {
        $body = $this->lastResponse->getBody()->toArray();

        PHPUnit::assertArrayNotHasKey('data', $body, 'Data key found in response body');
    }

    /**
     * @Given I should see :error in the error message
     */
    public function iShouldSeeInTheErrorMessage($expectedError)
    {
        PHPUnit::assertEquals($expectedError, $this->lastResponse->getBody()->getErrors()['message'], 'Data key found in response body');
    }

    /**
     * @Then I should receive a Forbidden response
     */
    public function iShouldReceiveAForbiddenResponse()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_403, $this->lastResponse->getStatusCode(), 'Did not receive 403 Forbidden response');
    }

    /**
     * @Then I should receive a Bad Request response
     */
    public function iShouldReceiveABadRequestResponse()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $this->lastResponse->getStatusCode(), 'Did not receive 400 Bad Request response');
    }
}
