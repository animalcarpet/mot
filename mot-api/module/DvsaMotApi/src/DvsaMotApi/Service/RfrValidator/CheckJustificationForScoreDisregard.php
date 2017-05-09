<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckJustificationForScoreDisregard.
 */
class CheckJustificationForScoreDisregard extends BaseValidator
{
    /**
     * Pattern for validation of an RFR.
     * - Do the relevant check,
     * - set the error if required
     * - return true if passed.
     *
     * @return bool|ErrorMessage
     */
    public function validate()
    {
        $this->validateTester();
        $this->validateVehicleExaminer();

        return $this->error === null;
    }

    /**
     * Criteria required.
     *
     * Should fail when..
     * Score is disregard
     * And justification is empty
     * AND the test type is Normal Test
     */
    protected function validateTester()
    {
        if (intval($this->values['score']) == self::SCORE_DISREGARD_VALUE
            && strlen($this->values['justification']) == 0
            && (isset($this->values['motTestType']) && $this->values['motTestType'] == 'NT')
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_MISSING_REQUIRED_JUSTIFICATION,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                array('mappedRfrs' => array($this->mappedRfrId => ['justification' => null]))
            );
        }
    }

    /**
     * Should fail when.
     *
     * Score is disregard
     * And justification is empty
     * And the RFR is NOT a getClass4RfrsNotTested
     * AND the test type is not a Normal Test
     */
    protected function validateVehicleExaminer()
    {
        if (intval($this->values['score']) == self::SCORE_DISREGARD_VALUE
            && strlen($this->values['justification']) == 0
            && !in_array($this->rfrId, BaseValidator::getClass4RfrsNotTested())
            && (isset($this->values['motTestType']) && $this->values['motTestType'] !== 'NT')
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_MISSING_REQUIRED_JUSTIFICATION,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                array('mappedRfrs' => array($this->mappedRfrId => array('justification' => null)))
            );
        }
    }
}
