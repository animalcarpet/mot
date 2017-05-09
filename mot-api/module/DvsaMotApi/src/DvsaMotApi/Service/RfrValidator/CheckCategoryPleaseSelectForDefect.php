<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckCategoryPleaseSelectForDefect.
 */
class CheckCategoryPleaseSelectForDefect extends BaseValidator
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
        if (intval($this->values['category']) === self::CATEGORY_PLEASE_SELECT
            && (intval($this->values['decision']) === self::DEFECT_MISSED
                || intval($this->values['decision']) === self::DEFECT_INCORRECT_DECISION)
        ) {
            $this->error = new ErrorMessage(
                self::INVALID_CATEGORY_FOR_DEFECT,
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['mappedRfrs' => [$this->mappedRfrId => ['category' => null]]]
            );
        }

        return $this->error === null;
    }
}
