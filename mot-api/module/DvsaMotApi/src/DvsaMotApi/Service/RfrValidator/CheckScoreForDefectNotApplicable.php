<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckScoreForCategoryNotApplicable.
 */
class CheckScoreForDefectNotApplicable extends BaseValidator
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
        $disallowedScores = [
            self::SCORE_DEFECT_MISSED_VALUE,
            self::SCORE_DAMAGE_MISSED_VALUE,
            self::SCORE_RISK_INJURY_MISSED_VALUE,
        ];

        if (in_array(intval($this->values['score']), $disallowedScores)
            && (
                intval($this->values['decision']) == self::DEFECT_NOT_APPLICABLE
                || intval($this->values['decision']) == self::DEFECT_INCORRECT_DECISION
            )
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_SCORE_FOR_DEFECT,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['mappedRfrs' => [$this->mappedRfrId => ['score' => null]]]
            );
        }

        return $this->error === null;
    }
}
