<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Month;
use DvsaCommon\Validator\BetweenDatesValidator;
use Zend\Validator\Date;

class StatisticsParameterCheck
{
    private $betweenDateValidator;

    public function __construct()
    {
        $pastDate = DateUtils::firstOfThisMonth()->sub(new \DateInterval("P13M"));
        $min = new Month($pastDate->format("Y"), $pastDate->format("m"));

        $firstOfThisMonth = DateUtils::firstOfThisMonth();
        $max = new Month($firstOfThisMonth->format("Y"), $firstOfThisMonth->format("m"));

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

        $date = new \DateTime();
        $date->setDate($year, $month, 1);
        if (!$this->betweenDateValidator->isValid($date)) {
            return false;
        }

        return true;
    }
}
