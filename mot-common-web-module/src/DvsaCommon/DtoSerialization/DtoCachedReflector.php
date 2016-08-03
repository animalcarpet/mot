<?php

namespace DvsaCommon\DtoSerialization;

use DvsaCommon\Utility\ArrayUtils;

class DtoCachedReflector implements DtoReflectorInterface
{
    private $reflector;

    /** @var DtoClassReflection */
    private $cachedReflections = [];

    public function __construct(DtoConvertibleTypesRegistry $convertiblesRegister)
    {
        $this->reflector = new DtoReflector($convertiblesRegister);
    }

    /**
     * @param $dtoClass
     *
     * @return DtoClassReflection
     */
    public function reflect($dtoClass)
    {
        $reflection = $this->getCachedReflection($dtoClass);

        if (!$reflection) {
            $reflection = $this->reflector->reflect($dtoClass);
            $this->cachedReflections[$dtoClass] = $reflection;
        }

        return $reflection;
    }

    /**
     * @param $class
     * @return DtoClassReflection|null
     */
    private function getCachedReflection($class)
    {
        return ArrayUtils::tryGet($this->cachedReflections, $class);
    }
}
