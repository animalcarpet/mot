<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckDecisionAndCategoryValuesExistForScore.
 */
class CheckCategoryExistsForScore extends BaseValidator
{
    /**
     * Ensure that if a score has been entered, then a category must be selected.
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
            && (empty($this->values['category'])
            || ($this->values['category'] == self::CATEGORY_NOT_APPLICABLE
                    && $this->values['score'] != self::SCORE_NOT_DEFECT))
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_CATEGORY_FOR_SCORE,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['mappedRfrs' => [$this->mappedRfrId => ['category' => null]]]
            );
        }

        return $this->error === null;
    }
}
