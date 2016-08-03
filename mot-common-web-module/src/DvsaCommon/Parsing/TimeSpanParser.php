<?php

namespace DvsaCommon\Parsing;

use DvsaCommon\Date\TimeSpan;

class TimeSpanParser
{
    public function toJsonString(TimeSpan $timeSpan)
    {
        if ($timeSpan->getDays() == 0) {
            return sprintf(
                "%02d:%02d:%02d",
                $timeSpan->getHours(),
                $timeSpan->getMinutes(),
                $timeSpan->getSeconds()
            );
        } else {
            return sprintf(
                "%d.%02d:%02d:%02d",
                $timeSpan->getDays(),
                $timeSpan->getHours(),
                $timeSpan->getMinutes(),
                $timeSpan->getSeconds()
            );
        }
    }

    public function fromJsonString($jsonString)
    {
        $jsonString = trim($jsonString);

        if (substr_count($jsonString, ":") != 2) {
            throw new WrongTimeSpanParserException("Failed parsing '" . $jsonString . "'. There should be two colons.");
        }

        if (substr_count($jsonString, ".") >= 2) {
            throw new WrongTimeSpanParserException("Failed parsing '" . $jsonString . "'. There should not be more than one dot.");
        }

        $days = 0;

        if (strpos($jsonString, '.')) {
            $parts = explode(".", $jsonString);
            $days = $parts[0];

            if (substr_count($days, ":") > 0) {
                throw new WrongTimeSpanParserException("Failed parsing '" . $jsonString . "'. There should be no colons before the dot.");
            }

            $time = $parts[1];
        } else {
            $time = $jsonString;
        }

        $timeParts = explode(":", $time);
        $hours = $timeParts[0];
        $minutes = $timeParts[1];
        $seconds = $timeParts[2];

        return new TimeSpan($days, $hours, $minutes, $seconds);
    }
}
