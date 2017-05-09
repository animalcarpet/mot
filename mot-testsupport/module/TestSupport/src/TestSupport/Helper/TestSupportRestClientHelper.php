<?php

namespace TestSupport\Helper;

use DvsaCommon\HttpRestJson\Client as JsonClient;

class TestSupportRestClientHelper
{
    /**
     * @var JsonClient
     */
    private $jsonClient;

    /**
     * @var array username => access token
     */
    private $tokenCache = [];

    /**
     * @var TestSupportAccessTokenManager
     */
    private $accessTokenManager;

    public function __construct(JsonClient $jsonClient, TestSupportAccessTokenManager $accessTokenManager)
    {
        $this->jsonClient = $jsonClient;
        $this->accessTokenManager = $accessTokenManager;
    }

    public function getJsonClient($data)
    {
        if (!isset($data['requestor'])) {
            $data['requestor'] = ['username' => 'schememgt', 'password' => 'Password1'];
        }

        list($requestorUsername, $requestorPassword) = RequestorParserHelper::parse($data);

        // usage of tokenCache was disabled because it was the main reason of behat tests instability on jenkins envs
        $accessToken = $this->accessTokenManager->getToken($requestorUsername, $requestorPassword);
        $this->tokenCache[$requestorUsername] = $accessToken;

        $this->jsonClient->setAccessToken($accessToken);

        return $this->jsonClient;
    }
}
