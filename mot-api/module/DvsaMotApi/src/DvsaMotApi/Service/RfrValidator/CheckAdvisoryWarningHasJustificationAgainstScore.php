<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckAdvisoryWarningHasJustificationAgainstScore.
 */
class CheckAdvisoryWarningHasJustificationAgainstScore extends BaseResultValidator
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
        if ((int) $this->values['caseOutcome'] === self::CASE_OUTCOME_ADVISORY_WARNING_LETTER
            && ($this->calculatedScore < BaseValidator::SCORE_SIGNIFICANTLY_WRONG_POINTS
                || $this->calculatedScore >= BaseValidator::SCORE_DAMAGE_MISSED_POINTS)
            && strlen(trim($this->values['finalJustification'])) === 0
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_MISSING_REQUIRED_JUSTIFICATION,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['finalJustification' => null]
            );
        }

        return $this->error === null;
    }
}
