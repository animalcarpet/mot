<?php

namespace DvsaCommon\ApiClient\Statistics\AePerformance\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class RiskAssessmentDto implements ReflectiveDtoInterface
{
    /** @var  float */
    private $score;
    /** @var  \DateTime */
    private $date;

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param float $score
     * @return RiskAssessmentDto
     */
    public function setScore($score)
    {
        $this->score = $score;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     * @return RiskAssessmentDto
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
}
