<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;

/**
 * Class AbstractValidatorTest.
 */
abstract class AbstractResultValidatorTest extends AbstractValidatorTest
{
    protected $fixtureCounter = 0;

    protected $fixtures = array();

    /**
     * The template pattern for testing a validator, do not override in subclasses
     * All validators will be tested the same, the only thing that changes is
     * the fixtures in getFixtures and the validator in getValidator.
     *
     * Fixtures are at the result level, so individual mapped rfrs will be in
     * the mappedRfrs element.
     */
    public function testValidate()
    {
        foreach ($this->getFixtures() as $fixture) {
            $mappedRfrId = null;
            $mappedRfrValues = null;

            if (is_array($fixture['mappedRfrs'])) {
                foreach ($fixture['mappedRfrs'] as $mappedRfrId => $mappedRfrValues) {
                    break;
                }
            }

            if ($mappedRfrId === null) {
                $this->fail('Fixture did not contain a mappedRfrId');
            }

            if ($mappedRfrValues === null) {
                $this->fail("Fixture->mappedRfr[{$mappedRfrId}] did not contain any values");
            }

            $validator = $this->getValidator($mappedRfrId, $fixture);
            $validationPassed = $validator->validate();

            $name = $this->getFixtureName();

            $msg = "{$name}:\n".
                "mappedRfrId/rfrId {$mappedRfrId}/".
                (isset($mappedRfrValues['rfrId']) ? "{$mappedRfrValues['rfrId']}: " : ': ').
                "caseOutcome: {$fixture['caseOutcome']}, ".
                "totalScore: {$fixture['totalScore']}, ".
                "finalJustification: '{$fixture['finalJustification']}'";

            if ($fixture['error'] === 1) {
                $this->assertFalse($validationPassed, 'Validation Passed? '.$msg);
                $error = $validator->getError();
                $this->assertInstanceOf(\DvsaCommonApi\Error\Message::class, $error, $msg);
                $this->assertEquals($error->message, $fixture['message'], $msg);
                $this->assertArrayHasKey(
                    $fixture['failedItem'],
                    $error->fieldDataStructure,
                    $msg
                );
            } else {
                $this->assertTrue($validationPassed, 'Validation Passed? '.$msg);
            }
        }
    }

    /**
     * @param bool $debug
     *
     * @return AbstractValidatorTest
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param      $score
     * @param      $decision
     * @param      $category
     * @param      $outcome
     * @param      $totalScore
     * @param bool $fitnesse
     */
    protected function addPassFixtureWithRfr($score, $decision, $category, $outcome, $totalScore, $fitnesse = false)
    {
        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[$this->fixtureCounter] = [
                    'rfrId' => $this->validRfrIds[$this->fixtureCounter],
                    'score' => $score,
                    'decision' => $decision,
                    'category' => $category,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => $outcome,
            'finalJustification' => '',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (int) $fitnesse,

        ];
        ++$this->fixtureCounter;

        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[$this->fixtureCounter] = [
                    'rfrId' => $this->validRfrIds[$this->fixtureCounter],
                    'score' => $score,
                    'decision' => $decision,
                    'category' => $category,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => $outcome,
            'finalJustification' => 'has a value',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (int) $fitnesse,
        ];
        ++$this->fixtureCounter;
    }

    /**
     * @param      $score
     * @param      $decision
     * @param      $category
     * @param      $outcome
     * @param      $totalScore
     * @param bool $fitnesse
     */
    protected function addFailFixtureWithRfr($score, $decision, $category, $outcome, $totalScore, $fitnesse = false)
    {
        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[$this->fixtureCounter] = [
                    'rfrId' => $this->validRfrIds[$this->fixtureCounter],
                    'score' => $score,
                    'decision' => $decision,
                    'category' => $category,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => $outcome,
            'finalJustification' => '',
            'totalScore' => $totalScore,
            'error' => 1,
            'message' => BaseValidator::INVALID_MISSING_REQUIRED_JUSTIFICATION,
            'failedItem' => 'finalJustification',
            'fitnesse' => (int) $fitnesse,

        ];
        ++$this->fixtureCounter;

        $this->fixtures[] = [
            'reinspectionMotTest' => 2037,
            'mappedRfrs' => [
                $this->validMappedRfrIds[$this->fixtureCounter] = [
                    'rfrId' => $this->validRfrIds[$this->fixtureCounter],
                    'score' => $score,
                    'decision' => $decision,
                    'category' => $category,
                    'justification' => 'we passed a value',
                    'error' => 0,
                    'message' => '',
                ],
            ],
            'caseOutcome' => $outcome,
            'finalJustification' => 'has a value',
            'totalScore' => $totalScore,
            'error' => 0,
            'fitnesse' => (int) $fitnesse,
        ];
        ++$this->fixtureCounter;
    }
}
