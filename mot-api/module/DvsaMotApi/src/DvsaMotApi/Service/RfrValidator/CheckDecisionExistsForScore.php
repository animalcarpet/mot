<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckDecisionAndCategoryValuesExistForScore.
 */
class CheckDecisionExistsForScore extends BaseValidator
{
    /**
     * Ensure that if a score has been entered, then a decision must be selected.
     *
     * @return bool
     */
    public function validate()
    {
        $checkedScores = [
            self::SCORE_OVERRULED_VALUE,
            self::SCORE_OBVIOUSLY_WRONG_VALUE,
            self::SCORE_SIGNIFICANTLY_WRONG_VALUE,
            self::SCORE_NOT_DEFECT,
            self::SCORE_DEFECT_MISSED_VALUE,
            self::SCORE_NOT_TESTABLE_VALUE,
            self::SCORE_DAMAGE_MISSED_VALUE,
            self::SCORE_RISK_INJURY_MISSED_VALUE,
        ];

        if (in_array($this->values['score'], $checkedScores)
            && (
                empty($this->values['decision']) || $this->values['decision'] == self::DEFECT_NOT_APPLICABLE
            )
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_DECISION_FOR_SCORE,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['mappedRfrs' => [$this->mappedRfrId => ['decision' => null]]]
            );
        }

        return $this->error === null;
    }
}
