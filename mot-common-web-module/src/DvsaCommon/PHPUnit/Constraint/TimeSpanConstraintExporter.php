<?php

namespace DvsaCommon\PHPUnit\Constraint;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Parsing\TimeSpanParser;
use SebastianBergmann\Exporter\Exporter;

class TimeSpanConstraintExporter extends Exporter
{
    /**
     * @param TimeSpan $value
     * @param int $indentation
     * @return string
     */
    public function export($value, $indentation = 0)
    {
        $timeSpanParser = new TimeSpanParser();

        return "TimeSpan " . $timeSpanParser->toJsonString($value);
    }

}
