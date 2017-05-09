<?php

namespace TestSupport\Helper;

use DvsaCommon\HttpRestJson\Client as JsonClient;

/**
 * Creates DvsaCommon\HttpRestJson\Client service with token.
 */
trait RestClientGetterTrait
{
    /**
     * Creates DvsaCommon\HttpRestJson\Client service with token.
     *
     * @param array $data
     *
     * @return JsonClient
     */
    private function getRestClientService($data)
    {
        list($schmUsername, $schmPassword) = RequestorParserHelper::parse($data);

        /**
         * @var JsonClient
         * @var TestSupportAccessTokenManager $accessTokenManager
         */
        $restClient = $this->getServiceLocator()->get(JsonClient::class);
        $accessTokenManager = $this->getServiceLocator()->get(TestSupportAccessTokenManager::class);

        $accessToken = $accessTokenManager->getToken($schmUsername, $schmPassword);
        $restClient->setAccessToken($accessToken);

        return $restClient;
    }
}
