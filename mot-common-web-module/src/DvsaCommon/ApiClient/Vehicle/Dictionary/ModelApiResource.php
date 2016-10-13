<?php
namespace DvsaCommon\ApiClient\Vehicle\Dictionary;

use DvsaCommon\ApiClient\Vehicle\Dictionary\Dto\ModelDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;
use DvsaCommon\UrlBuilder\UrlBuilder;

class ModelApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @return ModelDto[]
     */
    public function getList($makeId)
    {
        return $this->getMany(
            ModelDto::class,
            UrlBuilder::vehicleDictionary()->make($makeId)->models()->toString()
        );
    }
}
