<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

/**
 * Class DtoMapper.
 */
class DtoMapper
{
    protected $client;
    private $hydrator;

    public function __construct(HttpRestJsonClient $client)
    {
        $this->client = $client;
        $this->hydrator = new DtoHydrator();
    }

    protected function get($url)
    {
        $response = $this->client->get($url);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function getWithParams($url, $params)
    {
        $response = $this->client->getWithParams($url, $params);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function put($url, $data)
    {
        $response = $this->client->put($url, $data);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function post($url, $data)
    {
        $response = $this->client->post($url, $data);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function patch($url, $data)
    {
        $response = $this->client->patch($url, $data);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function delete($url)
    {
        $response = $this->client->delete($url);

        return $this->hydrator->doHydration($response['data']);
    }

    protected function getHydator()
    {
        return $this->hydrator;
    }
}
