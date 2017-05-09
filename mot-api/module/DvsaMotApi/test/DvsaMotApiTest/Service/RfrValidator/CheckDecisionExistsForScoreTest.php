<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\RfrValidator\CheckDecisionExistsForScore;

/**
 * Class CheckDecisionExistsForScoreTest.
 */
class CheckDecisionExistsForScoreTest extends AbstractValidatorTest
{
    /**
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return CheckDecisionExistsForScore|mixed
     */
    protected function getValidator($mappedRfrId, $fixture)
    {
        return new CheckDecisionExistsForScore($mappedRfrId, $fixture);
    }

    /**
     * Get the name for this fixture to appear in generated tests.
     *
     * @return array|string
     */
    public function getFixtureName()
    {
        return 'Check Decision Exists For Score';
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        $fixtures = array();
        $fixtures[$this->validMappedRfrIds[0]] = [
            'rfrId' => $this->validRfrIds[0],
            'score' => 0,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 0,
            'message' => '',
            'fitnesse' => 0,
        ];
        $fixtures[$this->validMappedRfrIds[1]] = [
            'rfrId' => $this->validRfrIds[1],
            'score' => 1,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 0,
            'message' => '',
            'fitnesse' => 0,
        ];
        $fixtures[$this->validMappedRfrIds[2]] = [
            'rfrId' => $this->validRfrIds[2],
            'score' => 2,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[3]] = [
            'rfrId' => $this->validRfrIds[3],
            'score' => 3,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[4]] = [
            'rfrId' => $this->validRfrIds[4],
            'score' => 3,
            'decision' => '',
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[5]] = [
            'rfrId' => $this->validRfrIds[5],
            'score' => 3,
            'decision' => null,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[6]] = [
            'rfrId' => $this->validRfrIds[6],
            'score' => 5,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[7]] = [
            'rfrId' => $this->validRfrIds[7],
            'score' => 6,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[8]] = [
            'rfrId' => $this->validRfrIds[8],
            'score' => 7,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[9]] = [
            'rfrId' => $this->validRfrIds[9],
            'score' => 8,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];
        $fixtures[$this->validMappedRfrIds[10]] = [
            'rfrId' => $this->validRfrIds[10],
            'score' => 9,
            'decision' => BaseValidator::DEFECT_PLEASE_SELECT,
            'category' => BaseValidator::CATEGORY_IMMEDIATE,
            'justification' => 'irrelevant for this test',
            'error' => 1,
            'message' => BaseValidator::INVALID_DECISION_FOR_SCORE,
            'failedItem' => 'decision',
            'fitnesse' => 1,
        ];

        return $fixtures;
    }
}
