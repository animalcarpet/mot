<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\BaseResultValidator;
use DvsaMotApi\Service\RfrValidator\CheckDisciplinaryActionHasJustificationAgainstScore;

/**
 * Class CheckAdvisoryWarningHasJustificationAgainstScoreTest.
 */
class CheckDisciplinaryActionHasJustificationAgainstScoreTest extends AbstractResultValidatorTest
{
    /**
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return CheckDisciplinaryActionHasJustificationAgainstScore|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        $mappedRfrId = null;

        return new CheckDisciplinaryActionHasJustificationAgainstScore($fixture, $fixture['totalScore']);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check Disciplinary Action Has Justification Against Score';
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        $this->makeFixtureWhereJustificationMustPass(0, BaseValidator::SCORE_DISREGARD_VALUE);
        $this->makeFixtureWhereJustificationMustPass(10, BaseValidator::SCORE_SIGNIFICANTLY_WRONG_VALUE);
        $this->makeFixtureWhereJustificationMustPass(20, BaseValidator::SCORE_NOT_DEFECT);
        $this->makeFixtureWhereJustificationMustPass(20, BaseValidator::SCORE_DEFECT_MISSED_VALUE);
        $this->makeFixtureWhereJustificationMustPass(20, BaseValidator::SCORE_NOT_TESTABLE_VALUE);
        $this->makeFixtureWhereJustificationMayPass(30, BaseValidator::SCORE_DAMAGE_MISSED_VALUE);
        $this->makeFixtureWhereJustificationMayPass(40, BaseValidator::SCORE_RISK_INJURY_MISSED_VALUE);

        return $this->fixtures;
    }

    /**
     * @param      $totalScore
     * @param      $scoreValue
     * @param bool $fitnesse
     */
    protected function makeFixtureWhereJustificationMustPass($totalScore, $scoreValue, $fitnesse = false)
    {
        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[count($this->fixtures)] = [
                    'rfrId' => $this->validRfrIds[count($this->fixtures)],
                    'score' => $scoreValue,
                    'decision' => BaseValidator::DEFECT_MISSED,
                    'category' => BaseValidator::CATEGORY_IMMEDIATE,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => BaseResultValidator::CASE_OUTCOME_DISCIPLINARY_ACTION_REPORT,
            'finalJustification' => '',
            'totalScore' => $totalScore,
            'error' => 1,
            'message' => BaseValidator::INVALID_MISSING_REQUIRED_JUSTIFICATION,
            'failedItem' => 'finalJustification',
            'fitnesse' => (bool) $fitnesse,
        ];
        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[count($this->fixtures)] = [
                    'rfrId' => $this->validRfrIds[count($this->fixtures)],
                    'score' => $scoreValue,
                    'decision' => BaseValidator::DEFECT_MISSED,
                    'category' => BaseValidator::CATEGORY_IMMEDIATE,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => BaseResultValidator::CASE_OUTCOME_DISCIPLINARY_ACTION_REPORT,
            'finalJustification' => 'has a value',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (bool) $fitnesse,
        ];
    }

    /**
     * @param int  $totalScore
     * @param      $scoreValue
     * @param bool $fitnesse
     *
     * @return array
     */
    protected function makeFixtureWhereJustificationMayPass($totalScore, $scoreValue, $fitnesse = false)
    {
        $fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[count($this->fixtures)] = [
                    'rfrId' => $this->validRfrIds[count($this->fixtures)],
                    'score' => $scoreValue,
                    'decision' => BaseValidator::DEFECT_MISSED,
                    'category' => BaseValidator::CATEGORY_IMMEDIATE,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => BaseResultValidator::CASE_OUTCOME_DISCIPLINARY_ACTION_REPORT,
            'finalJustification' => '',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (bool) $fitnesse,
        ];
        $fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[count($this->fixtures)] = [
                    'rdrId' => $this->validRfrIds[count($this->fixtures)],
                    'score' => BaseValidator::SCORE_DAMAGE_MISSED_VALUE,
                    'decision' => BaseValidator::DEFECT_MISSED,
                    'category' => BaseValidator::CATEGORY_IMMEDIATE,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => BaseResultValidator::CASE_OUTCOME_DISCIPLINARY_ACTION_REPORT,
            'finalJustification' => 'has a value',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (bool) $fitnesse,
        ];

        return $fixtures;
    }
}
