<?php

namespace CoreTest\Service;

use DvsaClient\MapperFactory;

class StubMapperFactory extends MapperFactory
{
    private $mappers;

    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    public function __get($class)
    {
        if (array_key_exists($class, $this->mappers)) {
            return $this->mappers[$class];
        }

        return null;
    }
}
