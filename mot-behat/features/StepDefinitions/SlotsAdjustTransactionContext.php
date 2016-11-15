<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\SlotPurchase;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class SlotsAdjustTransactionContext implements Context
{
    private $slotPurchase;
    private $userData;
    private $authorisedExaminerData;

    /**
     * @var array
     */
    private $amendmentReasonTypesMap = [
        'Failures'                         => 'T701',
        'Slot Refund'                      => 'T702',
        'Manual Adjustment of slots'       => 'T700',
        'Manual adjustment of transaction' => 'T703',
    ];

    private $reasonMap = [
        'wrongData' => 'IV',

    ];
    /**
     * @var Response
     */
    private $responseReceived;

    public function __construct(
        SlotPurchase $slotPurchase,
        UserData $userData,
        AuthorisedExaminerData $authorisedExaminerData
    )
    {
        $this->slotPurchase = $slotPurchase;
        $this->userData = $userData;
        $this->authorisedExaminerData = $authorisedExaminerData;
    }

    /**
     * @Given A slot transaction exist
     */
    public function aSlotTransactionExist()
    {
        $slots                  = rand(25, 500);
        $this->responseReceived = $this->slotPurchase->makePaymentForSlot(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $slots,
            $this->authorisedExaminerData->get()->getId()
        );
    }

    /**
     * @When I adjust the transaction to the correct Authorised Examiner :ae
     */
    public function iAdjustTheTransactionTotheCorrectAuthorisedExaminer(OrganisationDto $ae)
    {
        $data                   = $this->responseReceived->getBody();
        $transactionId          = $data['data']['transaction_id'];
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $body                   = [
            'organisationId' => $ae->getId(),
        ];
        $this->responseReceived = $this->slotPurchase->adjustTransaction($token, $transactionId, $body);
    }

    /**
     * @Then The transaction should be adjusted
     */
    public function theTransactionShouldBeCorrectlyAdjusted()
    {
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Unable to adjust transaction'
        );
    }

    /**
     * @When I adjust the transaction attribute :field to :value because of :reason
     */
    public function iAdjustTheTransactionAttributeTo($field, $value, $reason)
    {
        $data                   = $this->responseReceived->getBody();
        $transactionId          = $data['data']['transaction_id'];
        $token                  = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $body                   = [
            'statusCode' => $this->reasonMap[$reason],
            $field       => $value
        ];
        $this->responseReceived = $this->slotPurchase->adjustTransaction($token, $transactionId, $body);
    }

    /**
     * @When I request a list of amendment reasons by type ":type"
     */
    public function requestListOfAmendmentReasonsByType($type)
    {
            $token = $this->userData->getCurrentLoggedUser()->getAccessToken();

            $this->responseReceived = $this->slotPurchase->getAmendmentReasonsByType(
                $token, $this->amendmentReasonTypesMap[$type]
            );
    }

    /**
     * @Then I should have ":reason" available in the result
     */
    public function checkReasonPresentInResponse($reason)
    {
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->responseReceived->getStatusCode(),
            'Unable to adjust transaction'
        );

        $arrayOfReasons = array_values($this->responseReceived->getBody()->getData());

        PHPUnit::assertNotFalse(
            array_search($reason, $arrayOfReasons),
            'Unable to find reason for that type'
        );

    }


}
