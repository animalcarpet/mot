<?php

namespace Dvsa\Mot\Behat\Support\Helper;

use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\HttpRestJson\ZendClient;
use Symfony\Component\Yaml\Yaml;

class ApiResourceHelper
{
    private $client;

    public function __construct(ZendClient $client)
    {
        $this->client = $client;
    }

    public function create($class)
    {
        $r = new \ReflectionClass($class);
        return $r->newInstanceArgs([$this->client, new DtoReflectiveDeserializer(), new DtoReflectiveSerializer()]);
    }
}
