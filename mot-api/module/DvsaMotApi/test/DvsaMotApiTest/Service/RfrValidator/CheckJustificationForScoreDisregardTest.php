<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\CheckJustificationForScoreDisregard;

/**
 * Class CheckJustificationForScoreDisregardTest.
 */
class CheckJustificationForScoreDisregardTest extends AbstractValidatorTest
{
    /**
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return CheckJustificationForScoreDisregard|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        return new CheckJustificationForScoreDisregard($mappedRfrId, $fixture);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check Justification For Score Disregard';
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        $fixtures = array();

        $scores = range(1, 9);
        $counter = 0;
        // test pass/fails with no justification texts

        /*
         * Rules: FAIL
         *  - item is one of getClass4RfrsNotTested() AND
         *  - score is disregard AND
         *  - justification is empty if motTestType is NT
         */
        foreach ($scores as $score) {
            if ($score == 1) {
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => 8566,
                    'score' => 1,
                    'decision' => BaseValidator::DEFECT_NOT_APPLICABLE,
                    'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                    'justification' => '',
                    'motTestType' => 'VE',
                    'error' => 0,
                    'fitnesse' => 0,
                ];
                ++$counter;
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => $this->validRfrIds[$counter],
                    'score' => 1,
                    'decision' => BaseValidator::DEFECT_NOT_APPLICABLE,
                    'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                    'justification' => '',
                    'motTestType' => 'VE',
                    'error' => 1,
                    'message' => BaseValidator::INVALID_MISSING_REQUIRED_JUSTIFICATION,
                    'failedItem' => 'justification',
                    'fitnesse' => 1,
                ];
                ++$counter;
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => 8566,
                    'score' => 1,
                    'decision' => BaseValidator::DEFECT_NOT_APPLICABLE,
                    'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                    'justification' => '',
                    'motTestType' => 'NT',
                    'error' => 1,
                    'message' => BaseValidator::INVALID_MISSING_REQUIRED_JUSTIFICATION,
                    'failedItem' => 'justification',
                    'fitnesse' => 1,
                ];
            } else {
                $fixtures[$this->validMappedRfrIds[$counter]] = [
                    'rfrId' => $this->validRfrIds[$counter],
                    'score' => $score,
                    'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
                    'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                    'justification' => '',
                    'error' => 0,
                    'message' => '',
                    'fitnesse' => intval($score == $scores[1]),
                ];
            }
            ++$counter;
        }
        // test pass/fails with justification texts
        foreach ($scores as $score) {
            $fixtures[$this->validMappedRfrIds[$counter]] = [
                'rfrId' => $this->validRfrIds[$counter],
                'score' => $score,
                'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
                'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                'justification' => 'we passed a value',
                'error' => 0,
                'message' => '',
                'fitnesse' => intval($score == $scores[2]),
            ];
            ++$counter;
        }
        // make sure that 'not tested' RFRS do not fail when no justification passed and
        // when score is disregard
        foreach (BaseValidator::getClass4RfrsNotTested() as $notTestedRfr) {
            $fixtures[$this->validMappedRfrIds[$counter]] = [
                'rfrId' => $notTestedRfr,
                'score' => BaseValidator::SCORE_DISREGARD_VALUE,
                'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
                'category' => BaseValidator::CATEGORY_NOT_APPLICABLE,
                'justification' => '',
                'error' => 0,
                'message' => '',
            ];
        }

        return $fixtures;
    }
}
