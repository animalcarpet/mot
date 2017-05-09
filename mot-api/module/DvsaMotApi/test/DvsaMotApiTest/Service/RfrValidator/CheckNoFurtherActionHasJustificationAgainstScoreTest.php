<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\BaseResultValidator;
use DvsaMotApi\Service\RfrValidator\CheckNoFurtherActionHasJustificationAgainstScore;

/**
 * Class CheckNoFurtherActionHasJustificationAgainstScoreTest.
 */
class CheckNoFurtherActionHasJustificationAgainstScoreTest extends AbstractResultValidatorTest
{
    /**
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return CheckNoFurtherActionHasJustificationAgainstScore|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        $mappedRfrId = null;

        return new CheckNoFurtherActionHasJustificationAgainstScore($fixture, $fixture['totalScore']);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check No Further Action Has Justification Against Score';
    }

    /**
     * range 11-29 must have a justification.
     *
     * @return array
     */
    public function getFixtures()
    {
        $this->fixtures = array();

        // score of zero.. doesnt matter if justification or not
        $this->addPassFixtureWithRfr(
            BaseValidator::SCORE_DISREGARD_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            1
        );
        // score of 5.. doesnt matter if justification or not
        $this->addPassFixtureWithRfr(
            BaseValidator::SCORE_OBVIOUSLY_WRONG_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            5,
            true
        );
        // score of 10.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_SIGNIFICANTLY_WRONG_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            10,
            true
        );
        // score of 11-29.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_DEFECT_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            20
        );
        // score of 29.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_NOT_TESTABLE_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            29
        );
        // score of 30 and above.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_DAMAGE_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            30
        );
        // score of 40.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_RISK_INJURY_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_NO_FURTHER_ACTION,
            40
        );

        return $this->fixtures;
    }
}
