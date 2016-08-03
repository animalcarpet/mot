<?php

namespace DvsaCommon\DtoSerialization\Convertion;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\DtoConverterInterface;
use DvsaCommon\Parsing\TimeSpanParser;
use DvsaCommon\Utility\TypeCheck;

class DtoTimeSpanConverter implements DtoConverterInterface
{
    public function jsonToObject($json)
    {
        $parser = new TimeSpanParser();

        return $parser->fromJsonString($json);
    }

    /**
     * @param TimeSpan $timeSpan
     * @return array
     */
    public function objectToJson($timeSpan)
    {
        TypeCheck::assertInstance($timeSpan, TimeSpan::class);

        $parser = new TimeSpanParser();

        return $parser->toJsonString($timeSpan);
    }

}
