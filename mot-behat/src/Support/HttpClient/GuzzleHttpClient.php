<?php

namespace Dvsa\Mot\Behat\Support\HttpClient;

use Dvsa\Mot\Behat\Support\HttpClient;
use Dvsa\Mot\Behat\Support\Request;
use Dvsa\Mot\Behat\Support\Response;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Message\Response as GuzzleResponse;

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
            $guzzleRequest = $this->client->createRequest(
                $request->getMethod(),
                $request->getUri(),
                $request->getHeaders(),
                $request->getBody()
            );

            $guzzleResponse = $guzzleRequest->send();
        } catch (ServerErrorResponseException $e) {
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
        return new Response(
            $request,
            $guzzleResponse->getStatusCode(),
            $guzzleResponse->getHeaders()->toArray(),
            json_decode($guzzleResponse->getBody(true), true),
            $guzzleResponse->getBody()
        );
    }
}
