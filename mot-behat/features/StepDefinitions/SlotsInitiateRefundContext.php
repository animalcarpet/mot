<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class SlotsInitiateRefundContext implements Context
{
    /**
     * @var SlotPurchase
     */
    protected $slotPurchase;
    /**
     * @var SessionContext
     */
    protected $sessionContext;
    /**
     * @var array
     */
    protected $organisationMap = [
        'kwikfit'  => 10,
        'halfords' => 1
    ];
    /**
     * @var Response
     */
    protected $responseReceived;

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
     * @Given I bought :slots slots for organisation :organisation at :price price
     */
    public function iBoughtSlotsForOrganisationAtPrice($slots, $organisation, $price)
    {
        $this->responseReceived = $this->slotPurchase->makePaymentForSlot(
            $this->sessionContext->getCurrentAccessToken(), $slots, $this->organisationMap[$organisation], $price
        );
    }

    /**
     * @When I search for the payment with a valid invoice
     */
    public function iSearchForThePaymentWithAValidInvoice()
    {
        $invoice                = 'MOT-20131231-784309AB';
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $this->responseReceived = $this->slotPurchase->searchByInvoiceNumber($token, $invoice);
    }

    /**
     * @When I search for the payment with an invalid invoice
     */
    public function iSearchForThePaymentWithAnInvalidInvoice()
    {
        $invoice                = 'NGT-00001231-784309AB';
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $this->responseReceived = $this->slotPurchase->searchByInvoiceNumber($token, $invoice);
    }

    /**
     * @Then I should receive invoice details
     */
    public function iShouldReceiveInvoiceDetails()
    {
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Refund was not rejected'
        );

        $body = $this->responseReceived->getBody();

        PHPUnit::assertArrayHasKey('data', $body);
        PHPUnit::assertArrayHasKey('transactions', $body['data']);
        PHPUnit::assertArrayHasKey('found', $body['data']);
        PHPUnit::assertTrue($body['data']['found']);
        PHPUnit::assertGreaterThanOrEqual(1, count($body['data']['transactions']));
    }

    /**
     * @Then I should not receive invoice details
     */
    public function iShouldNotReceiveInvoiceDetails()
    {
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Refund was not rejected'
        );

        $body = $this->responseReceived->getBody();

        PHPUnit::assertArrayHasKey('data', $body);
        PHPUnit::assertArrayHasKey('found', $body['data']);
        PHPUnit::assertFalse($body['data']['found']);
    }

    /**
     * @When I initiate the request to make a card payment
     */
    public function iInitiateTheRequestToMakeACardPayment()
    {
        $token                  = $this->sessionContext->getCurrentAccessToken();
        $responseReceived       = $this->slotPurchase->makePaymentForSlot(
            $token, 120, $this->organisationMap['kwikfit'], 2.05
        );
        $body                   = $responseReceived->getBody();
        $this->responseReceived = $this->slotPurchase->getRedirectionData(
            $token, 120, 2.05, $body['data']['sales_reference']
        );
        $body                   = $this->responseReceived->getBody();
        PHPUnit::assertArrayHasKey('data', $body);
    }

    /**
     * @Then I should receive :parameter parameter in the data returned
     */
    public function iShouldReceiveParameterInTheDataReturned($parameter)
    {
        $body = $this->responseReceived->getBody();
        PHPUnit::assertArrayHasKey($parameter, $body['data']);
    }
}
