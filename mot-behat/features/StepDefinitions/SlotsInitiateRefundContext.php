<?php

use Behat\Behat\Context\Context;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class SlotsInitiateRefundContext implements Context
{
    protected $slotPurchase;
    protected $userData;
    protected $authorisedExaminerData;

    /**
     * @var Response
     */
    protected $responseReceived;

    public function __construct(SlotPurchase $slotPurchase, UserData $userData, AuthorisedExaminerData $authorisedExaminerData)
    {
        $this->slotPurchase = $slotPurchase;
        $this->userData = $userData;
        $this->authorisedExaminerData = $authorisedExaminerData;
    }

    /**
     * @Given I bought :slots slots for organisation :ae at :price price
     */
    public function iBoughtSlotsForOrganisationAtPrice($slots, OrganisationDto $ae, $price)
    {
        $this->responseReceived = $this->slotPurchase->makePaymentForSlot(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $slots,
            $ae->getId(),
            $price
        );
    }

    /**
     * @When I search for the payment with a valid invoice
     */
    public function iSearchForThePaymentWithAValidInvoice()
    {
        $invoice                = 'MOT-20131231-784309AB';
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $this->responseReceived = $this->slotPurchase->searchByInvoiceNumber($token, $invoice);
    }

    /**
     * @When I search for the payment with an invalid invoice
     */
    public function iSearchForThePaymentWithAnInvalidInvoice()
    {
        $invoice                = 'NGT-00001231-784309AB';
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
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
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $responseReceived       = $this->slotPurchase->makePaymentForSlot(
            $token, 120, $this->authorisedExaminerData->get()->getId(), 2.05
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
