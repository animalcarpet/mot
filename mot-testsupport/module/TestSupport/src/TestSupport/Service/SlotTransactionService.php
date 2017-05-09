<?php

namespace TestSupport\Service;

use DvsaCommon\HttpRestJson\Client;
use TestSupport\Helper\TestSupportAccessTokenManager as TokenManager;

/**
 * Service to deal with accounts in system.
 */
class SlotTransactionService
{
    /**
     * @var \DvsaCommon\HttpRestJson\Client
     */
    private $restClient;

    /**
     * @var TokenManager
     */
    private $tokenManager;

    public function __construct(Client $restClient, TokenManager $tokenManager)
    {
        $this->restClient = $restClient;
        $this->tokenManager = $tokenManager;
    }

    /**
     * Create slot transaction calling the backend API.
     *
     * @param $organisation
     * @param $slots
     * @param $amount
     * @param $paymentType
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function createSlotTransaction($organisation, $slots, $amount, $paymentType)
    {
        /** @var Client $restClient */
        $password = 'Password1';
        $accessToken = $this->tokenManager->getToken('schememgt', $password);

        $this->restClient->setAccessToken($accessToken);
        $result = $this->restClient->post(
            'slots/transaction',
            [
                'organisation' => $organisation,
                'slots' => $slots,
                'amount' => $amount,
                'paymentType' => $paymentType,
            ]
        );

        return $result['data'];
    }
}
