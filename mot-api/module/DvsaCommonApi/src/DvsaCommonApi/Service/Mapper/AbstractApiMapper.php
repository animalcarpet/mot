<?php

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class AbstractApiMapper.
 */
abstract class AbstractApiMapper
{
    private $dtoHydrator;

    public function __construct()
    {
        $this->dtoHydrator = new DtoHydrator();
    }

    abstract public function toDto($object);

    public function manyToDto($objects)
    {
        return ArrayUtils::map(
            $objects, function ($object) {
                return $this->toDto($object);
            }
        );
    }

    public function toArray($object)
    {
        $dto = $this->toDto($object);

        return $this->dtoHydrator->extract($dto);
    }

    public function manyToArray($objects)
    {
        $dtos = $this->toDto($objects);

        return $this->dtoHydrator->extract($dtos);
    }
}
