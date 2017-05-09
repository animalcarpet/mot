<?php

namespace DvsaEntities\DataConversion;

abstract class AbstractStringConverter
{
    protected $charMapping = [];

    /**
     * @param $input
     *
     * @return string
     */
    public function convert($input)
    {
        if (is_string($input)) {
            return strtr(strtoupper($input), $this->charMapping);
        }
    }
}
