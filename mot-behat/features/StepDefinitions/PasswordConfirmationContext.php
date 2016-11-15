<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use PHPUnit_Framework_Assert as PHPUnit;

class PasswordConfirmationContext implements Context
{
    /**
     * @var Response
     */
    private $confirmSessionResponse;

    private $session;

    private $userData;

    public function __construct(
        Session $session,
        UserData $userData
    )
    {
        $this->session = $session;
        $this->userData = $userData;
    }

    /**
     * @Given I need to confirm my password before changing sensitive data
     */
    public function iNeedToConfirmMyPasswordBeforeChangingSensitiveData()
    {
        // nothing to do, gherkin information only
    }

    /**
     * @When I supply the correct password
     */
    public function iSupplyTheCorrectPassword()
    {
        $this->submitConfirmSessionRequest('Password1');
    }

    /**
     * @Then password verification should be successful
     */
    public function passwordVerificationShouldBeSuccessful()
    {
        PHPUnit::assertEquals(200, $this->confirmSessionResponse->getStatusCode());
    }

    /**
     * @When I supply the incorrect password
     */
    public function iSupplyTheIncorrectPassword()
    {
        try {
            $this->submitConfirmSessionRequest('NotThePassword');
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $this->confirmSessionResponse = $this->session->getLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Then password verification should not be successful
     */
    public function passwordVerificationShouldNotBeSuccessful()
    {
        PHPUnit::assertEquals(422, $this->confirmSessionResponse->getStatusCode());
    }

    private function submitConfirmSessionRequest($password)
    {
        $user = $this->userData->getCurrentLoggedUser();

        $this->confirmSessionResponse = $this->session->confirmSession($user->getAccessToken(), $password);
    }
}
