<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\CheckScoreForDefectNotApplicable;

/**
 * Class CheckScoreForCategoryNotApplicableTest.
 */
class CheckScoreForDefectNotApplicableTest extends AbstractValidatorTest
{
    /**
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return CheckScoreForDefectNotApplicable|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        return new CheckScoreForDefectNotApplicable($mappedRfrId, $fixture);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check Score For Defect Not Applicable';
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        /**
 * generate fixtures...
 * doing this by hand is crazy so here we go..
 *
 * Rules:
 * All Defect scores must pass apart from when
 *  -Defect "Not Applicable" and Score is 20, 30 or 40.
 * - Defect "Incorrect Decision" and Score is 20, 30 or 40.
 */
        //$this->debug = true;
        $defects = [
            BaseValidator::DEFECT_PLEASE_SELECT,
            BaseValidator::DEFECT_NOT_APPLICABLE,
            BaseValidator::DEFECT_MISSED,
            BaseValidator::DEFECT_INCORRECT_DECISION,
        ];
        $fixtures = [];

        $counter = 0;
        foreach ($defects as $defect) {
            $validScores = range(1, 9);
            $invalidScores = [];

            if ($defect == BaseValidator::DEFECT_NOT_APPLICABLE ||
                $defect == BaseValidator::DEFECT_INCORRECT_DECISION) {
                $validScores = [1, 2, 3, 4, 5, 7];
                $invalidScores = [
                    BaseValidator::SCORE_DEFECT_MISSED_VALUE,
                    BaseValidator::SCORE_DAMAGE_MISSED_VALUE,
                    BaseValidator::SCORE_RISK_INJURY_MISSED_VALUE,
                ];
            }

            // check valid for current defect
            foreach ($validScores as $validScore) {
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => $this->validRfrIds[$counter],
                    'score' => $validScore,
                    'decision' => $defect,
                    'category' => BaseValidator::CATEGORY_INSPECTION_NOTICE,
                    'justification' => 'irrelevant for this test',
                    'error' => 0,
                    'fitnesse' => intval($validScore == $validScores[0]),
                ];
                ++$counter;
            }

            // check invalid for current defect
            foreach ($invalidScores as $invalidScore) {
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => $this->validRfrIds[$counter],
                    'score' => $invalidScore,
                    'decision' => $defect,
                    'category' => BaseValidator::CATEGORY_INSPECTION_NOTICE,
                    'justification' => 'irrelevant for this test',
                    'error' => 1,
                    'message' => BaseValidator::INVALID_SCORE_FOR_DEFECT,
                    'failedItem' => 'score',
                    'fitnesse' => intval($validScore == $invalidScore[0]),
                ];
                ++$counter;
            }
        }

        //$json = json_encode($fixtures);
        return $fixtures;
    }
}
