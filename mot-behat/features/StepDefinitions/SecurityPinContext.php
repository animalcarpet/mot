<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\SecurityPin;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;
use Zend\Http\Response as HttpResponse;


class SecurityPinContext implements Context
{
    private $securityPin;
    private $siteData;
    private $userData;


    public function __construct(
        SecurityPin $securityPin,
        SiteData $siteData,
        UserData $userData
    )
    {
        $this->securityPin = $securityPin;
        $this->siteData = $siteData;
        $this->userData = $userData;
    }

    /**
     * @When /^I request a new security pin$/
     */
    public function iRequestANewSecurityPin()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->securityPin->resetPin($user->getUserId(), $user->getAccessToken());
    }

    /**
     * @Given /^the generated pin should be a number$/
     */
    public function theGeneratedPinShouldBeANumber()
    {
        $response = $this->securityPin->getLastResponse();
        $pin = $response->getBody()->getData()['pin'];
        PHPUnit::assertTrue(is_numeric($pin), 'Security pin is not a number.');
    }

    /**
     * @When /^I try request a new security pin for a (.*) user$/
     */
    public function iRequestANewSecurityPinForAUser($role)
    {
        if ($role == 'ao1') {
            $user = $this->userData->createAreaOffice1User();
        } elseif ($role == 'ao2') {
            $user = $this->userData->createAreaOffice2User();
        } elseif ($role == 'tester') {
            $user = $this->userData->createTester("Walter White");
        } elseif ($role == 'csco') {
            $user = $this->userData->createCustomerServiceOperator();
        } else {
            throw new \InvalidArgumentException('Role ' . $role . ' has not been implemented');
        }

        try {
            $this->securityPin->resetPin(
                $user->getUserId(),
                $this->userData->getCurrentLoggedUser()->getAccessToken()
            );

        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then /^I should not receive a new security pin$/
     */
    public function iShouldNotReceiveANewSecurityPin()
    {
        $response = $this->securityPin->getLastResponse();
        PHPUnit::assertEquals($response->getBody()->getErrors()['message'], 'Can only reset your own PIN');
    }

    /**
     * @Given /^the generated pin should be (\d+) digits long$/
     * @param $length
     */
    public function theGeneratedPinShouldBeDigitsLong($length)
    {
        $response = $this->securityPin->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode(), 'Did not receive 200 OK response');

        $pin = $response->getBody()->getData()['pin'];
        PHPUnit::assertEquals($length, strlen($pin), 'Pin is not ' . $length . ' digits long.');
    }
}
