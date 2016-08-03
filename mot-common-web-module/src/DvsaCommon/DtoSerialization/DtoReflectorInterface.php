<?php

namespace DvsaCommon\DtoSerialization;

/**
 * Interface DtoReflectorInterface
 * @package DvsaCommon\DtoSerialization
 *
 * Creates reflection of a DTO to allow serialisation and de-serialisation of DTO.
 */
interface DtoReflectorInterface
{
    /**
     * @param $dtoClass
     *
     * @return DtoClassReflection
     */
    public function reflect($dtoClass);
}
