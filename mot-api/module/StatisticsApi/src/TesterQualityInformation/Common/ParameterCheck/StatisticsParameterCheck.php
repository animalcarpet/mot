<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck;

use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\Month;
use DvsaCommon\Validator\BetweenDatesValidator;
use Zend\Validator\Date;

class StatisticsParameterCheck
{
    private $betweenDateValidator;
    private $dateTimeHolder;

    public function __construct(DateTimeHolderInterface $dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;

        $pastDate = $dateTimeHolder->getCurrentDate()->sub(new \DateInterval("P13M"));
        $min = new Month($pastDate->format("Y"), $pastDate->format("m"));

        $currentDate = $dateTimeHolder->getCurrentDate();
        $max = new Month($currentDate->format("Y"), $currentDate->format("m"));

        $this->betweenDateValidator = new BetweenDatesValidator($min->getEndDate(), $max->getStartDate());
    }

    public function isValid($year, $month)
    {
        if (!is_int($year) || !is_int($month)) {
            return false;
        }

        $dateValidator = new Date();
        if (!$dateValidator->isValid([$year, $month, 1])) {
            return false;
        }

        $date = $this->dateTimeHolder->getCurrentDate();
        $date->setDate($year, $month, 1);
        if (!$this->betweenDateValidator->isValid($date)) {
            return false;
        }

        return true;
    }
}
