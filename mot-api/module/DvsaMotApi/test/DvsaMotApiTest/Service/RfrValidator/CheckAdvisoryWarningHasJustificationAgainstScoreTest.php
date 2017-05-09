<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\BaseResultValidator;
use DvsaMotApi\Service\RfrValidator\CheckAdvisoryWarningHasJustificationAgainstScore;

/**
 * Class CheckAdvisoryWarningHasJustificationAgainstScoreTest.
 */
class CheckAdvisoryWarningHasJustificationAgainstScoreTest extends AbstractResultValidatorTest
{
    /**
     * @param null $mappedRfrId
     * @param      $fixture
     *
     * @return CheckAdvisoryWarningHasJustificationAgainstScore|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        $mappedRfrId = null;

        return new CheckAdvisoryWarningHasJustificationAgainstScore($fixture, $fixture['totalScore']);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check Advisory Warning Has Justification Against Score';
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        // score of zero.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_DISREGARD_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            0
        );
        // score of 5.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_OBVIOUSLY_WRONG_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            0
        );
        // score of 10.. Must not have justification
        $this->addPassFixtureWithRfr(
            BaseValidator::SCORE_SIGNIFICANTLY_WRONG_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            10,
            true
        );
        // score of 11-29.. doesn't matter if no justification
        $this->addPassFixtureWithRfr(
            BaseValidator::SCORE_DEFECT_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            20,
            true
        );
        // score of 29.. Must have justification
        $this->addPassFixtureWithRfr(
            BaseValidator::SCORE_NOT_TESTABLE_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            29,
            true
        );

        // score of 30 and above.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_DAMAGE_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            30
        );
        // score of 40.. Must have justification
        $this->addFailFixtureWithRfr(
            BaseValidator::SCORE_RISK_INJURY_MISSED_VALUE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::CATEGORY_IMMEDIATE,
            BaseResultValidator::CASE_OUTCOME_ADVISORY_WARNING_LETTER,
            40
        );

        return $this->fixtures;
    }
}
