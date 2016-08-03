<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\HttpClient;
use Dvsa\Mot\Behat\Support\Request;

class MotApi
{
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_GET    = 'GET';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PATCH   = 'PATCH';

    protected $client;

    const ACCEPT_APPLICATION_JSON = 'application/json';
    const ACCEPT_APPLICATION_PDF = 'application/pdf';

    const CONTENT_APPLICATION_JSON = 'application/json';

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $token
     * @param string $method
     * @param string $path
     * @param array $params
     * @param string $accept header
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function sendRequest($token, $method, $path, array $params = null, $accept = self::ACCEPT_APPLICATION_JSON)
    {
        $body = null !== $params ? json_encode($params) : null;

        return $this->client->request(new Request(
            $method,
            $path,
            [
                'Content-Type'  => self::CONTENT_APPLICATION_JSON,
                'Accept'        => $accept,
                'Authorization' => 'Bearer ' . $token
            ],
            $body
        ));
    }
    /**
     * @param string $token
     * @param string $method
     * @param string $path
     * @param array  $params
     *
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function pdfRequest($token, $method, $path, array $params = null)
    {
        return $this->sendRequest($token, $method, $path, $params, self::ACCEPT_APPLICATION_PDF);
    }
}
