<?php

namespace DvsaMotApi\Controller\Validator;

use DvsaCommon\Enum\EnfDecisionReinspectionOutcomeId;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;

class ReinspectionReportValidator
{
    const FIELD_OUTCOME = 'reinspection-outcome';
    const ERROR_MSG_OUTCOME_REQUIRED = 'Please enter a valid reinspection outcome';

    protected $data;

    /**
     * Ctor: Saves the data for subsequent validation.
     *
     * @param $data Array
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Validates the current data set.
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate()
    {
        $isValid = false;
        $outcomePresent = ArrayUtils::hasNotEmptyValue($this->data, self::FIELD_OUTCOME);

        if ($outcomePresent) {
            $isValid = $this->isValidOutcomeValue($this->data[self::FIELD_OUTCOME]);
        }

        if (!$outcomePresent || !$isValid) {
            throw new BadRequestException(
                self::ERROR_MSG_OUTCOME_REQUIRED,
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }
    }

    /**
     * Answers true if $value is a recognised reinspection outcome value.
     *
     * @param $value
     *
     * @return bool
     */
    public function isValidOutcomeValue($value)
    {
        return EnfDecisionReinspectionOutcomeId::exists($value);
    }

    /**
     * @return string
     */
    public function getOutcome()
    {
        return $this->data[self::FIELD_OUTCOME];
    }
}
