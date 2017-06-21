<?php

namespace Dvsa\Mot\Behat\Support\HttpClient;

use Dvsa\Mot\Behat\Support\HttpClient;
use Dvsa\Mot\Behat\Support\Request;
use Dvsa\Mot\Behat\Support\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface as GuzzleResponse;

class GuzzleHttpClient implements HttpClient
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function request(Request $request)
    {
        $guzzleResponse = null;

        try {
            $guzzleResponse = $this->client->request(
                $request->getMethod(),
                $request->getUriAsSting(),
                [
                    "headers" => $request->getHeaders(),
                    "body" => $request->getBody()
                ]
            );

        } catch (ServerException $e) {
            $guzzleResponse = $e->getResponse();
        } catch (BadResponseException $e) {
            $guzzleResponse = $e->getResponse();
        }

        return $this->createResponse($request, $guzzleResponse);
    }

    /**
     * @param Request        $request
     * @param GuzzleResponse $guzzleResponse
     *
     * @return Response
     */
    private function createResponse(Request $request, GuzzleResponse $guzzleResponse)
    {
        $bodyContents = $guzzleResponse->getBody()->getContents();
        return new Response(
            $request,
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getHeaders(),
            json_decode($bodyContents, true),
            $bodyContents
        );
    }
}
