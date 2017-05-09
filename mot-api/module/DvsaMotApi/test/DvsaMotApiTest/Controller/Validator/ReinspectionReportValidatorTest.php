<?php

namespace DvsaMotApiTest\Controller\Validator;

use DvsaCommon\Enum\EnfDecisionReinspectionOutcomeId;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Controller\Validator\ReinspectionReportValidator;

/**
 * Class ReinspectionReportValidatorTest.
 */
class ReinspectionReportValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testReinspectionReportValidatorGetOutcome()
    {
        $outcome = EnfDecisionReinspectionOutcomeId::AGREED_FULLY_WITH_TEST_RESULT;
        $v = new ReinspectionReportValidator(
            [
                'reinspection-outcome' => $outcome,
            ]
        );
        $this->assertEquals($outcome, $v->getOutcome());
    }

    public function testReinspectionReportValidatorIsValidOutcomeValue()
    {
        $outcome = EnfDecisionReinspectionOutcomeId::AGREED_FULLY_WITH_TEST_RESULT;
        $v = new ReinspectionReportValidator(
            [
                'reinspection-outcome' => $outcome,
            ]
        );
        $v->validate();
        $this->assertEquals(true, $v->isValidOutcomeValue($outcome));
    }

    public function testReinspectionReportValidatorWithInvalidData()
    {
        $outcome = 'invalidData';
        $this->setExpectedException(BadRequestException::class);
        $v = new ReinspectionReportValidator(
            [
                'reinspection-outcome' => $outcome,
            ]
        );
        $v->validate();
        $this->assertEquals(false, $v->isValidOutcomeValue($outcome));
    }
}
