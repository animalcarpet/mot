<?php

namespace TestSupport\Helper;

use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Temporarily copied from Fitnesse (TestShared.php) until OpenAM is in.
 */
class TestSupportAccessTokenManager
{
    private $tokenCache = [];

    private $apiUrl;
    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getToken($username, $password)
    {
        $curlHandle = curl_init($this->apiUrl.UrlBuilder::of()->session()->toString());

        self::setupCurlOptions($curlHandle);
        $postFields = [
            'username' => $username,
            'password' => $password,
        ];
        self::setCurlPost($curlHandle, $postFields);

        $jsonResult = self::execCurlForJson($curlHandle);

        $accessToken = null;

        if (array_key_exists('errors', $jsonResult)) {
            $error = http_build_query($jsonResult);
            throw new \Exception("Could not login to system with credentials $username:$password, result: $error");
        }
        $accessToken = $jsonResult->{'data'}->{'accessToken'};

        $this->tokenCache[$username.';'.$password] = $accessToken;

        return $accessToken;
    }

    public static function addSchemeManagerAsRequestorIfNecessary(&$data)
    {
        if (!isset($data['requestor'])) {
            $data['requestor'] = ['username' => 'schememgt', 'password' => 'Password1'];
        }
    }

    public function invalidateTokens()
    {
        $curlHandle = curl_init($this->apiUrl.UrlBuilder::of()->session()->toString());

        foreach ($this->tokenCache as $token) {
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, 'DELETE');
            $this->setupCurlOptions($curlHandle);
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER,
                ['Content-Type: application/json',
                 'Authorization: Bearer '.$token, ]);
            $this->execCurlForJson($curlHandle);
        }
    }

    private function execCurlForJson($curlHandle, $shouldClose = true)
    {
        $curlResult = curl_exec($curlHandle);

        $curlInfo = curl_getinfo($curlHandle);

        $curlError = curl_error($curlHandle);
        if ($curlError != '') {
            throw new \Exception("Curl exception: [$curlError]");
        }

        if ($shouldClose) {
            curl_close($curlHandle);
        }

        $contentType = null;
        if (array_key_exists('content_type', $curlInfo)) {
            $contentType = $curlInfo['content_type'];
        }

        if (!strstr($contentType, 'application/json')) {
            error_log("Unexpected response: [$curlResult]");
            throw new \Exception("Expected to get JSON, got [$contentType]; response body: [$curlResult]");
        }

        $jsonResult = json_decode($curlResult);

        if (!$jsonResult) {
            error_log("Unable to parse response: [$curlResult]");
            throw new \Exception("Unable to parse response: [$curlResult]");
        }

        return $jsonResult;
    }

    private function setupCurlOptions($curlHandle)
    {
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_HEADER, false);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'testsupport;phpcurl;DVSA-MOT');
    }

    private static function setCurlPost($curlHandle, $data)
    {
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, json_encode($data));
    }
}
