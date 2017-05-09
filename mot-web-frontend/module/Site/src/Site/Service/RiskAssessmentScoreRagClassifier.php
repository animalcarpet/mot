<?php

namespace Site\Service;

use DvsaCommon\Configuration\MotConfig;

class RiskAssessmentScoreRagClassifier
{
    const WHITE_VALUE = 0;

    const WHITE_STATUS = 'White';
    const GREEN_STATUS = 'Green';
    const AMBER_STATUS = 'Amber';
    const RED_STATUS = 'Red';

    const PRECISION = 2;

    /** @var float $score */
    private $score;

    /** @var array $config */
    private $config;

    /** @var float $greenStart */
    private $greenStart;

    /** @var float $amberStart */
    private $amberStart;

    /** @var float $redStart */
    private $redStart;

    /**
     * @param int|float|string $score  Risk assessment score
     * @param MotConfig        $config
     */
    public function __construct($score, MotConfig $config)
    {
        $this->setScore($score);
        $this->greenStart = $config->get('site_assessment', 'green', 'start');
        $this->amberStart = $config->get('site_assessment', 'amber', 'start');
        $this->redStart = $config->get('site_assessment', 'red', 'start');
    }

    /**
     * @return string
     */
    public function getRagScore()
    {
        if ($this->isWhite()) {
            return self::WHITE_STATUS;
        }

        if ($this->isGreen()) {
            return self::GREEN_STATUS;
        }

        if ($this->isAmber()) {
            return self::AMBER_STATUS;
        }

        return self::RED_STATUS;
    }

    /**
     * @return bool
     */
    public function isWhite()
    {
        return $this->isEqual(self::WHITE_VALUE);
    }
    /**
     * @return bool
     */
    public function isGreen()
    {
        return $this->isBetween($this->greenStart, $this->amberStart);
    }

    /**
     * @return bool
     */
    public function isAmber()
    {
        return $this->isBetween($this->amberStart, $this->redStart);
    }

    /**
     * @return bool
     */
    public function isRed()
    {
        return $this->isGreaterOrEqualThan($this->redStart);
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param int|float|string $score Risk assessment score
     *
     * @return $this
     */
    public function setScore($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('Score should be an numeric value');
        }

        $this->score = floatval($score);

        return $this;
    }

    /**
     * @param $lowerBound
     * @param $upperBound
     *
     * @return bool
     */
    private function isBetween($lowerBound, $upperBound)
    {
        return
            $this->isGreaterOrEqualThan($lowerBound) &&
            $this->isLessThan($upperBound)
        ;
    }

    /**
     * @param $lowerBound
     *
     * @return bool
     */
    private function isGreaterOrEqualThan($lowerBound)
    {
        return bccomp($this->score, $lowerBound, self::PRECISION) >= 0;
    }

    /**
     * @param $upperBound
     *
     * @return bool
     */
    private function isLessThan($upperBound)
    {
        return bccomp($this->score, $upperBound, self::PRECISION) == -1;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function isEqual($value)
    {
        return bccomp($this->score, $value, self::PRECISION) == 0;
    }
}
