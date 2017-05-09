<?php

namespace DvsaMotApi\Service\RfrValidator;

use DvsaMotApi\Service\RfrValidator\BaseValidator;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\Exception\BadRequestException;

/**
 * Class CheckCategoryAllowedForDefectNotApplicable.
 */
class CheckCategoryAllowedForDefectNotApplicable extends BaseValidator
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
        if (intval($this->values['decision']) === self::DEFECT_NOT_APPLICABLE &&
            (intval($this->values['category']) === self::CATEGORY_IMMEDIATE ||
                intval($this->values['category']) === self::CATEGORY_DELAYED ||
                intval($this->values['category']) === self::CATEGORY_INSPECTION_NOTICE)
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
