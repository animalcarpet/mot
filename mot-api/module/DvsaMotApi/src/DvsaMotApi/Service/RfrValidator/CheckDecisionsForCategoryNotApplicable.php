<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckDecisionsForCategoryNotApplicable.
 */
class CheckDecisionsForCategoryNotApplicable extends BaseValidator
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
        if (intval($this->values['category']) === self::CATEGORY_NOT_APPLICABLE
            && (intval($this->values['decision']) === self::DEFECT_MISSED)
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_DEFECT_FOR_CATEGORY,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['mappedRfrs' => [$this->mappedRfrId => ['decision' => null]]]
            );
        }

        return $this->error === null;
    }
}
