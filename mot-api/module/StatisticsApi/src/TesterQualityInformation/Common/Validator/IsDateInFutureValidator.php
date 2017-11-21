<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Validator;

use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\DateUtils;

class IsDateInFutureValidator
{
    private $dateTimeHolder;

    public function __construct(DateTimeHolderInterface $dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function isValid(\DateTime $dateTime)
    {
        return DateUtils::cropTime($dateTime) > DateUtils::cropTime($this->dateTimeHolder->getCurrent());
    }
}
