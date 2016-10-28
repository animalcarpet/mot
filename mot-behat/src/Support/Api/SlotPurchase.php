<?php

namespace Dvsa\Mot\Behat\Support\Api;

use DateTime;
use Dvsa\Mot\Behat\Support\Response;

class SlotPurchase extends MotApi
{
    const END_POINT_SLOT_PAYMENT = 'slots/add-instant-settlement';
    const END_POINT_TRANSACTION_SEARCH = 'slots/transaction/search/%s';
    const END_POINT_ADJUSTMENT = 'slots/amendment/%s/adjustment';
    const END_POINT_ADJUSTMENT_REASON_TYPE = 'slots/amendment-reason/type/%s';
    const END_POINT_REDIRECTION_DATE = 'slots/redirection-data';
    const END_POINT_PAYMENT_REFRESH = 'slots/payment-refresh/%s';
    const END_POINT_PAYMENT_DETAILS = 'slots/report/details/payment/%s';
    const END_POINT_MANUAL_ADJUSTMENT = 'slots/adjustment';

    const MANUAL_ADJUSTMENT_TYPE_POSITIVE = 'positive';
    const MANUAL_ADJUSTMENT_TYPE_NEGATIVE = 'negative';

    /**
     * Adjust transaction
     *
     * @param       $transactionId
     * @param       $token
     * @param array $body
     *
     * @return Response
     */
    public function adjustTransaction($token, $transactionId, $body = [])
    {
        $url = sprintf(self::END_POINT_ADJUSTMENT, $transactionId);

        return $this->sendRequest($token, 'POST', $url, $body);
    }

    /**
     * @param       $token
     * @param       $slots
     * @param       $organisation
     * @param float $price
     *
     * @return Response
     */
    public function makePaymentForSlot($token, $slots, $organisation, $price = 2.05)
    {
        $amount = number_format($price * $slots, 2, '.', '');
        $body   = [
            'organisation' => $organisation,
            'slots'        => $slots,
            'amount'       => $amount,
            'paidAmount'   => $amount,
            'paymentType'  => 'cheque',
            'autoRefund'   => false,
            'paymentData'  => [
                'accountName'  => 'Tester',
                'chequeNumber' => rand(10000, 99999),
                'slipNumber'   => rand(20000, 99999),
                'chequeDate'   => (new DateTime('-10 days'))->format('Y-m-d'),
            ]

        ];

        return $this->sendRequest($token, 'POST', self::END_POINT_SLOT_PAYMENT, $body);
    }

    /**
     * @param $token
     * @param $invoiceNumber
     *
     * @return Response
     */
    public function searchByInvoiceNumber($token, $invoiceNumber)
    {
        $param = [
            'type' => 2
        ];
        $url   = sprintf(self::END_POINT_TRANSACTION_SEARCH, $invoiceNumber) . '?' . http_build_query($param);

        return $this->sendRequest($token, 'GET', $url);
    }

    /**
     * @param $token
     * @param $type
     *
     * @return Response
     */
    public function getAmendmentReasonsByType($token, $type)
    {
        $url = sprintf(self::END_POINT_ADJUSTMENT_REASON_TYPE, $type);

        return $this->sendRequest($token, 'GET', $url);
    }

    /**
     * @param $token
     * @param $slots
     * @param $price
     * @param $invoiceNumber
     *
     * @return Response
     */
    public function getRedirectionData($token, $slots, $price, $invoiceNumber)
    {
        $amount = number_format($price * $slots, 2, '.', '');
        $body   = [
            'scope'   => 'CARD',
            'payload' => [
                'customer_reference' => 'AE555',
                'total_amount'       => $amount,
                'scope'              => 'CARD',
                'user_id'            => 'test',
                'customer_name'      => 'MOT User',
                'redirect_uri'       => 'http://mot-web-frontend.mot.gov.uk',
                'cost_centre'        => '12345,90987',
                'payment_data'       => [
                    [
                        'sales_reference'   => $invoiceNumber,
                        'amount'            => $amount,
                        'product_reference' => 'MOT_SLOTS',
                    ]
                ]
            ],
        ];

        return $this->sendRequest($token, 'POST', self::END_POINT_REDIRECTION_DATE, $body);
    }

    /**
     * @param $token
     * @param $receiptReference
     * @return Response
     */
    public function refreshPayment($token, $receiptReference)
    {
        return $this->sendRequest(
            $token,
            'POST',
            sprintf(self::END_POINT_PAYMENT_REFRESH, $receiptReference)
        );
    }

    /**
     * @param $token
     * @param $transactionId
     * @return Response
     */
    public function getPaymentDetails($token, $transactionId)
    {
        return $this->sendRequest(
            $token,
            'GET',
            sprintf(self::END_POINT_PAYMENT_DETAILS, $transactionId)
        );
    }

    /**
     * @param $token
     * @param $organisation_id
     * @param string $type
     * @param string $reason
     * @param string $comments
     * @param int $slots
     * @return Response
     */
    public function makeManualAdjustment(
        $token,
        $organisation_id,
        $type = self::MANUAL_ADJUSTMENT_TYPE_POSITIVE,
        $reason = 'some reason',
        $comments = 'some comment',
        $slots = 100
    ) {
        return $this->sendRequest($token, 'POST', self::END_POINT_MANUAL_ADJUSTMENT, [
            'type' => $type,
            'reason' => $reason,
            'comments' => $comments,
            'slots' => $slots,
            'organisation_id' => $organisation_id,
        ]);
    }
}
