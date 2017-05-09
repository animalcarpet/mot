<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use DvsaMotApiTest\Service\AbstractMotTestServiceTest;
use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaMotApi\Service\EnforcementMotTestResultService;

/**
 * Class CheckAllValidatorsTest.
 */
class CheckAllValidatorsTest extends AbstractMotTestServiceTest
{
    public function testAllValidatorsAsService()
    {
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $service = new EnforcementMotTestResultService(
            $this->getMockEntityManager(),
            $this->getMockHydrator(),
            $this->getMockAuthorizationService(),
            $mockMotTestMapper
        );

        $allFixtures = $this->getAllFixtures();

        foreach ($allFixtures as $className => $values) {
            $name = $values['name'];

            // When the test run together we need to adjust the fixtures they are only true when run independently.
            // fix flaws in fixtures (when merged)..
            if ($className == \DvsaMotApiTest\Service\RfrValidator\CheckScoreForDefectNotApplicableTest::class) {
                foreach ($values['fixtures'] as $mappedRfrId => $fixture) {
                    if ($fixture['score'] >= 2) {
                        if ($fixture['decision'] == 1 && $fixture['category'] == 4) {
                            $values['fixtures'][$mappedRfrId]['message'] = BaseValidator::INVALID_DECISION_FOR_SCORE;
                            $values['fixtures'][$mappedRfrId]['error'] = 1;
                            $values['fixtures'][$mappedRfrId]['failedItem'] = 'decision';
                            $values['fixtures'][$mappedRfrId]['notes'] = 'forced in unit';
                        }
                    } else {
                        if ($fixture['decision'] == 1 && $fixture['category'] == 4) {
                            $values['fixtures'][$mappedRfrId]['message'] = BaseValidator::INVALID_CATEGORY_FOR_DEFECT;
                            $values['fixtures'][$mappedRfrId]['error'] = 1;
                            $values['fixtures'][$mappedRfrId]['failedItem'] = 'category';
                            $values['fixtures'][$mappedRfrId]['notes'] = 'forced in unit';
                        }
                    }
                }
            }

            // Make sure all the fixtures have a decision and a score if there is a positive score
            // Because of: CheckDecisionExistsForScore
            foreach ($values['fixtures'] as $mappedRfrId => &$fixture) {
                if (empty($fixture['decision']) && $fixture['score'] >= 2) {
                    $fixture['error'] = 1;
                    $fixture['message'] = BaseValidator::INVALID_DECISION_FOR_SCORE;
                    $fixture['failedItem'] = 'decision';
                    $fixture['notes'] = 'forced in unit';
                }
                // Because of: CheckCategoryExistsForScore
                if (empty($fixture['category']) && $fixture['score'] >= 2) {
                    $fixture['error'] = 1;
                    $fixture['message'] = BaseValidator::INVALID_CATEGORY_FOR_SCORE;
                    $fixture['failedItem'] = 'category';
                    $fixture['notes'] = 'forced in unit';
                }
            }

            foreach ($values['fixtures'] as $mappedRfrId => $fixture) {
                $msg = "{$name}: ".
                    "{$mappedRfrId}, ".
                    "{$fixture['rfrId']}, ".
                    "{$fixture['score']}, ".
                    "{$fixture['decision']}, ".
                    "{$fixture['category']}, ".
                    "'{$fixture['justification']}'";

                $error = $service->validateMappedRfr($mappedRfrId, $fixture);

                if ($fixture['error'] === 1) {
                    $this->assertInstanceOf(\DvsaCommonApi\Error\Message::class, $error, $msg);
                    $this->assertEquals($error->message, $fixture['message'], $msg);
                    $this->assertArrayHasKey(
                        $mappedRfrId,
                        $error->fieldDataStructure['mappedRfrs'],
                        $msg
                    );
                    $this->assertArrayHasKey(
                        $fixture['failedItem'],
                        $error->fieldDataStructure['mappedRfrs'][$mappedRfrId],
                        $msg
                    );
                } else {
                    $this->assertNull($error, $msg);
                }
            }
        }
    }

    /**
     * @return CheckScoreForCategoryNotApplicable
     */
    protected function getValidatorTests()
    {
        $tests = [];
        $tests[] = new CheckCategoryAllowedForDefectNotApplicableTest();
        $tests[] = new CheckCategoryPleaseSelectForDefectTest();
        $tests[] = new CheckDecisionsForCategoryNotApplicableTest();
        $tests[] = new CheckJustificationForScoreDisregardTest();
        $tests[] = new CheckScoreForDefectNotApplicableTest();

        return $tests;
    }

    /**
     * @return array
     */
    public function getAllFixtures()
    {
        $tests = $this->getValidatorTests();
        $fixtures = [];
        foreach ($tests as $test) {
            $fixtures[get_class($test)] = [
                'name' => $test->getFixtureName(),
                'fixtures' => $test->getFixtures(),
            ];
        }

        return $fixtures;
    }
}
