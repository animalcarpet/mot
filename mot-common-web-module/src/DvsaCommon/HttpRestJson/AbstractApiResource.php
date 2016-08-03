<?php

namespace DvsaCommon\HttpRestJson;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;

class AbstractApiResource
{
    protected $httpClient;
    protected $deserializer;
    protected $serializer;

    public function __construct(Client $httpClient, DtoReflectiveDeserializer $deserializer, DtoReflectiveSerializer $serializer)
    {
        $this->httpClient = $httpClient;
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
    }

    protected function getSingle($returnedDtoClass, $url, $params = [])
    {
        $data = $this->httpClient->getWithParamsReturnDto($url, $params)['data'];

        return $this->deserializer->deserialize($data, $returnedDtoClass);
    }

    protected function getMany($returnedDtoClass, $url)
    {
        $response = $this->httpClient->get($url);

        return $this->deserializer->deserializeArray($response['data'], $returnedDtoClass);
    }
}
