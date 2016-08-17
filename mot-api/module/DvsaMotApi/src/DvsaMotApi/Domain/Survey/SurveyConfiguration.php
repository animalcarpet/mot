<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Domain\Survey;

/**
 * Class SurveyConfiguration holds configuration for the GDS Survey functionality.
 */
class SurveyConfiguration
{
    const KEY__NUMBER_OF_TESTS_BETWEEN_SURVEYS = 'numberOfTestsBetweenSurveys';
    const KEY__TIME_BEFORE_SURVEY_REDISPLAYED = 'timeBeforeSurveyRedisplayed';
    const DEFAULT__NUMBER_OF_TESTS_BETWEEN_SURVEYS = 10000;
    const DEFAULT__TIME_BEFORE_SURVEY_REDISPLAYED = '3 months';

    /**
     * @var int
     */
    private $numberOfTestsBetweenSurveys;

    /**
     * @var string
     */
    private $timeBeforeSurveyRedisplayed;

    /**
     * SurveyConfiguration constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->numberOfTestsBetweenSurveys = isset($config[self::KEY__NUMBER_OF_TESTS_BETWEEN_SURVEYS]) ?
            intval($config[self::KEY__NUMBER_OF_TESTS_BETWEEN_SURVEYS]) : self::DEFAULT__NUMBER_OF_TESTS_BETWEEN_SURVEYS ;

        $this->timeBeforeSurveyRedisplayed = isset($config[self::KEY__TIME_BEFORE_SURVEY_REDISPLAYED]) ?
            $config[self::KEY__TIME_BEFORE_SURVEY_REDISPLAYED] : self::DEFAULT__TIME_BEFORE_SURVEY_REDISPLAYED;
    }

    /**
     * @return int
     */
    public function getNumberOfTestsBetweenSurveys()
    {
        return $this->numberOfTestsBetweenSurveys;
    }

    /**
     * @return string
     */
    public function getTimeBeforeSurveyRedisplayed()
    {
        return $this->timeBeforeSurveyRedisplayed;
    }
}