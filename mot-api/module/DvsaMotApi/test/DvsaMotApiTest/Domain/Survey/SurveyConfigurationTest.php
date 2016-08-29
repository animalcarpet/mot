<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Domain\Survey;

use DvsaMotApi\Domain\Survey\SurveyConfiguration;
use PHPUnit_Framework_TestCase;

class SurveyConfigurationTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultValuesAreUsedWhenConfigKeysAreMissing()
    {
        $config = [];
        $surveyConfiguration = new SurveyConfiguration($config);
        $this->assertEquals(SurveyConfiguration::DEFAULT__NUMBER_OF_TESTS_BETWEEN_SURVEYS, $surveyConfiguration->getNumberOfTestsBetweenSurveys());
        $this->assertEquals(SurveyConfiguration::DEFAULT__TIME_BEFORE_SURVEY_REDISPLAYED, $surveyConfiguration->getTimeBeforeSurveyRedisplayed());
    }

    public function testConfigIsPreserved()
    {
        $config = [
            SurveyConfiguration::KEY__NUMBER_OF_TESTS_BETWEEN_SURVEYS => 1,
            SurveyConfiguration::KEY__TIME_BEFORE_SURVEY_REDISPLAYED => '1 day',
        ];

        $surveyConfiguration = new SurveyConfiguration($config);
        $this->assertEquals(1, $surveyConfiguration->getNumberOfTestsBetweenSurveys());
        $this->assertEquals('1 day', $surveyConfiguration->getTimeBeforeSurveyRedisplayed());
    }

    public function testStringsAreCastedToIntForNumberOfTestsBetweenSurveyEntries()
    {
        $config = [
            SurveyConfiguration::KEY__NUMBER_OF_TESTS_BETWEEN_SURVEYS => '10',
        ];

        $surveyConfiguration = new SurveyConfiguration($config);
        $this->assertEquals(10, $surveyConfiguration->getNumberOfTestsBetweenSurveys());
    }
}
