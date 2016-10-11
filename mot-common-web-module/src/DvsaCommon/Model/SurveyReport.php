<?php

namespace DvsaCommon\Model;

class SurveyReport
{
    /**
     * @var \DateTime
     */
    private $timeStamp;

    /**
     * @var string
     */
    private $period;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var int
     */
    private $verySatisfiedRatings;

    /**
     * @var int
     */
    private $satisfiedRatings;

    /**
     * @var int
     */
    private $neitherSatisfiedNorDissatisfiedRatings;

    /**
     * @var int
     */
    private $dissatisfiedRatings;

    /**
     * @var int
     */
    private $veryDissatisfiedRatings;

    /**
     * SurveyReport constructor.
     *
     * @param $timeStamp
     * @param $period
     * @param $slug
     * @param $verySatisfiedRatings
     * @param $satisfiedRatings
     * @param $neitherSatisfiedNorDissatisfiedRatings
     * @param $dissatisfiedRatings
     * @param $veryDissatisfiedRatings
     */
    public function __construct(
        $timeStamp,
        $period,
        $slug,
        $verySatisfiedRatings,
        $satisfiedRatings,
        $neitherSatisfiedNorDissatisfiedRatings,
        $dissatisfiedRatings,
        $veryDissatisfiedRatings
    ) {
        $this->timeStamp = $timeStamp;
        $this->period = $period;
        $this->slug = $slug;
        $this->verySatisfiedRatings = $verySatisfiedRatings;
        $this->satisfiedRatings = $satisfiedRatings;
        $this->neitherSatisfiedNorDissatisfiedRatings = $neitherSatisfiedNorDissatisfiedRatings;
        $this->dissatisfiedRatings = $dissatisfiedRatings;
        $this->verySatisfiedRatings = $veryDissatisfiedRatings;
    }

    /**
     * @return \DateTime
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @param \DateTime $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }

    /**
     * @return string
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param string $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return int
     */
    public function getVerySatisfiedRatings()
    {
        return $this->verySatisfiedRatings;
    }

    /**
     * @param int $verySatisfiedRatings
     */
    public function setVerySatisfiedRatings($verySatisfiedRatings)
    {
        $this->verySatisfiedRatings = $verySatisfiedRatings;
    }

    /**
     * @return int
     */
    public function getSatisfiedRatings()
    {
        return $this->satisfiedRatings;
    }

    /**
     * @param int $satisfiedRatings
     */
    public function setSatisfiedRatings($satisfiedRatings)
    {
        $this->satisfiedRatings = $satisfiedRatings;
    }

    /**
     * @return int
     */
    public function getNeitherSatisfiedNorDissatisfiedRatings()
    {
        return $this->neitherSatisfiedNorDissatisfiedRatings;
    }

    /**
     * @param int $neitherSatisfiedNorDissatisfiedRatings
     */
    public function setNeitherSatisfiedNorDissatisfiedRatings($neitherSatisfiedNorDissatisfiedRatings)
    {
        $this->neitherSatisfiedNorDissatisfiedRatings = $neitherSatisfiedNorDissatisfiedRatings;
    }

    /**
     * @return int
     */
    public function getDissatisfiedRatings()
    {
        return $this->dissatisfiedRatings;
    }

    /**
     * @param int $dissatisfiedRatings
     */
    public function setDissatisfiedRatings($dissatisfiedRatings)
    {
        $this->dissatisfiedRatings = $dissatisfiedRatings;
    }

    /**
     * @return int
     */
    public function getVeryDissatisfiedRatings()
    {
        return $this->veryDissatisfiedRatings;
    }

    /**
     * @param int $veryDissatisfiedRatings
     */
    public function setVeryDissatisfiedRatings($veryDissatisfiedRatings)
    {
        $this->veryDissatisfiedRatings = $veryDissatisfiedRatings;
    }
}
