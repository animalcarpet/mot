<?php

namespace DvsaCommon\ApiClient\Vehicle\Dictionary;

use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;
use DvsaCommon\UrlBuilder\UrlBuilder;

class MakeApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @return MakeDto[]
     */
    public function getList()
    {
        return $this->getMany(
            MakeDto::class,
            UrlBuilder::vehicleDictionary()->make()->toString()
        );
    }
}
