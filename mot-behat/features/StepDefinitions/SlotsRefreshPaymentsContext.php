<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use PHPUnit_Framework_Assert as PHPUnit;

class SlotsRefreshPaymentsContext implements Context
{
    /**
     * @var \Dvsa\Mot\Behat\Support\Api\SlotPurchase
     */
    protected $slotPurchase;

    /**
     * @var SessionContext
     */
    protected $sessionContext;

     /**
     * @var Response
     */
    protected $responseReceived;

    /**
     * @var Response
     */
    protected $paymentDetails;

    /**
     * @param SlotPurchase $slotPurchase
     */
    public function __construct(SlotPurchase $slotPurchase)
    {
        $this->slotPurchase = $slotPurchase;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Given /^I have payment with status (.*) and is (.*) minutes old$/
     */
    public function iHavePaymentWithStatusAndIsMinutesOld($status, $minutes)
    {
        $this->responseReceived = $this->slotPurchase->makePaymentForSlot(
            $this->sessionContext->getCurrentAccessToken(),
            100,
            10
        );

        $transactionId        = $this->responseReceived->getBody()->getData()['transaction_id'];
        $this->paymentDetails = $this->slotPurchase->getPaymentDetails(
            $this->sessionContext->getCurrentAccessToken(),
            $transactionId
        )->getBody()->toArray();
    }

    /**
     * @When I attempt to refresh the payment status
     */
    public function iAttemptToRefreshThePaymentStatus()
    {
        $receiptReference = $this->paymentDetails['data']['receipt_reference'];

        $this->responseReceived = $this->slotPurchase->refreshPayment(
            $this->sessionContext->getCurrentAccessToken(),
            $receiptReference
        );
    }

    /**
     * @Then I should get valid message from refresh endpoint
     */
    public function iShouldGetValidMessageFromRefreshEndpoint()
    {
        PHPUnit::assertArrayHasKey('data', $this->responseReceived->getBody()->toArray());
        PHPUnit::assertArrayHasKey('message', $this->responseReceived->getBody()->getData());
    }
}
