<?php

namespace DvsaCommon\Date;

use DateTime;

interface DateRangeInterface
{
    public function getStartDate(): DateTime;

    public function getEndDate(): DateTime;

    public function getNumberOfMonths(): int;
}
