<?php
namespace Site\ViewModel;

use DvsaCommon\Date\TimeSpan;

class TimeSpanFormatter
{
    public function formatForTestQualityInformationView(TimeSpan $timeSpan)
    {
        if($timeSpan->getTotalMinutes() > 0) {
            return $timeSpan->getTotalMinutes();
        } elseif($timeSpan->getSeconds() > 0) {
            return 1;
        } else {
            return '';
        }
    }
}