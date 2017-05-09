<?php

namespace Application\Data;

use DvsaCommon\HttpRestJson\Client;

/**
 * Class ApiResources.
 */
class ApiResources
{
    protected $restClient;

    public function __construct(Client $restClient)
    {
        $this->restClient = $restClient;
    }

    protected function restSave($resourceUrl, $data)
    {
        return $this->restClient->postJson($resourceUrl, $data);
    }

    protected function restUpdate($resourceUrl, $data)
    {
        return $this->restClient->putJson($resourceUrl, $data);
    }

    protected function restGet($resourceUrl)
    {
        return $this->restClient->get($resourceUrl);
    }

    protected function restDelete($resourceUrl)
    {
        return $this->restClient->delete($resourceUrl);
    }
}
