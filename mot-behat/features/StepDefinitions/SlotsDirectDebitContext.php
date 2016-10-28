<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\DirectDebit;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class SlotsDirectDebitContext implements Context
{
    /**
     * @var DirectDebit
     */
    private $directDebit;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    private $slotPrice = 2.05;
    /**
     * @var Response
     */
    private $responseReceived;

    /**
     * @var  string
     */
    private $mandateReference;

    private $userData;

    /**
     * @param DirectDebit $directDebit
     */
    public function __construct(DirectDebit $directDebit, UserData $userData)
    {
        $this->directDebit = $directDebit;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Given I have an active direct debit mandate set up for :slots slots in :ae on :day
     */
    public function iHaveAnActiveDirectDebitMandateSetUpFor($slots, OrganisationDto $ae, $day)
    {
        $token           = $this->sessionContext->getCurrentAccessToken();
        $this->setUpDirectDebitMandate($token, $ae->getId(), $slots, $day);
    }

    /**
     * @Given :ae has active direct debit mandate set up for :slots slots on :day
     */
    public function hasActiveDirectDebitMandateSetUpForSlotsOn(OrganisationDto $ae, $slots, $day)
    {
        $aedm = $this->userData->getAedmByAeId($ae->getId());
        $this->setUpDirectDebitMandate($aedm->getAccessToken(), $ae->getId(), $slots, $day);
    }

    private function setUpDirectDebitMandate($token, $orgId, $slots, $day)
    {
        $mandateResponse = $this->directDebit->getActiveMandate($token, $orgId);
        $mandateBody     = $mandateResponse->getBody();

        if (empty($mandateBody['data']['mandate_id'])) {

            $this->responseReceived = $this->directDebit->setUpDirectDebitMandate(
                $token,
                $orgId,
                $slots,
                $day,
                number_format($slots * $this->slotPrice, 2, '.', '')
            );

            PHPUnit::assertEquals(
                HttpResponse::STATUS_CODE_200,
                $this->responseReceived->getStatusCode(),
                'Unable to setup mandate'
            );
            PHPUnit::assertArrayNotHasKey(
                'validationError',
                $this->responseReceived->getBody()->toArray(),
                'Did not expect any errors while setting up a direct debit mandate'
            );
        }
    }

    /**
     * @Given I have an active direct debit mandate for :ae
     */
    public function iHaveAnActiveDirectDebitMandate(OrganisationDto $ae)
    {
        $token           = $this->sessionContext->getCurrentAccessToken();
        $mandateResponse = $this->directDebit->getActiveMandate($token, $ae->getId());
        $mandateBody     = $mandateResponse->getBody();

        if (isset($mandateBody['data']['mandate_id'])) {
            $this->mandateReference = $mandateBody['data']['mandate_id'];
        } else {
            throw new \Exception('No mandate found');
        }

        if ($mandateBody['data']['status']['code'] == 'C') {
            $this->directDebit->completeMandateSetup(
                $token, $ae->getId(), $mandateBody['data']['mandate_id']
            );
        }
    }

    /**
     * @When I request to cancel the direct debit for :ae
     */
    public function iRequestToCancelTheDirectDebit(OrganisationDto $ae)
    {
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $this->responseReceived = $this->directDebit->cancelDirectDebit(
            $token,
            $ae->getId(),
            $this->mandateReference
        );
    }

    /**
     * @Then The direct debit should be inactive
     */
    public function theDirectDebitShouldBeInactive()
    {
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Unable to cancel the direct debit'
        );

        $body = $this->responseReceived->getBody();

        PHPUnit::assertArrayHasKey('data', $body);
        PHPUnit::assertArrayHasKey('success', $body['data']);
    }

    /**
     * @Then My direct debit should not be canceled
     */
    public function myDirectDebitShouldNotBeCanceled()
    {
        $body = $this->responseReceived->getBody();
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Direct request was not rejected'
        );

        PHPUnit::assertArrayHasKey('validationError', $body);
        PHPUnit::assertArrayHasKey('code', $body['validationError']);
    }

    /**
     * @When I setup direct debit of :numberOfSlots slots for :ae on :dayOfMonth day of the month
     */
    public function iSetupDirectDebitOfSlotsForAsdaOnDayOfTheMonth($numberOfSlots, OrganisationDto $ae, $dayOfMonth)
    {
        $token           = $this->sessionContext->getCurrentAccessToken();
        $mandateResponse = $this->directDebit->getActiveMandate($token, $ae->getId());
        $mandateBody     = $mandateResponse->getBody();

        if (isset($mandateBody['data']['mandate_id'])) {
            //Cancel existing mandate
            $this->directDebit->cancelDirectDebit(
                $token,
                $ae->getId(),
                $mandateBody['data']['mandate_id']
            );
        }

        $this->responseReceived = $this->directDebit->setUpDirectDebitMandate(
            $token,
            $ae->getId(),
            $numberOfSlots,
            $dayOfMonth,
            number_format($numberOfSlots * $this->slotPrice, 2, '.', '')
        );
    }

    /**
     * @Then The direct debit should not be setup
     */
    public function theDirectDebitShouldNotBeSetup()
    {
        $responseBody = $this->responseReceived->getBody();
        PHPUnit::assertArrayHasKey('validationError', $responseBody);
        PHPUnit::assertArrayHasKey('code', $responseBody['validationError']);
        PHPUnit::assertSame(433, $responseBody['validationError']['code']);
    }
}
