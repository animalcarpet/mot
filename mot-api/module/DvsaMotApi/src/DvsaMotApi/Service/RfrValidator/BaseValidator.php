<?php

namespace DvsaMotApi\Service\RfrValidator;

/**
 * Class BaseValidator.
 */
class BaseValidator
{
    const INVALID_DEFECT_FOR_CATEGORY = 'Invalid Defect for Category';
    const INVALID_DEFECT_FOR_CATEGORY_NOT_SELECTED = 'Invalid Defect for Category, Not Selected';
    const INVALID_CATEGORY_FOR_DEFECT = 'Invalid Category for Defect';
    const INVALID_SCORE_FOR_DEFECT = 'Invalid Score for Defect';
    const INVALID_MISSING_REQUIRED_JUSTIFICATION = 'Missing required Justification';
    const INVALID_DECISION_FOR_SCORE = 'Missing required Decision';
    const INVALID_CATEGORY_FOR_SCORE = 'Missing required Category';

    const CATEGORY_PLEASE_SELECT = 0;
    const CATEGORY_NOT_APPLICABLE = 1;
    const CATEGORY_IMMEDIATE = 2;
    const CATEGORY_DELAYED = 3;
    const CATEGORY_INSPECTION_NOTICE = 4;

    const DEFECT_PLEASE_SELECT = 0;
    const DEFECT_NOT_APPLICABLE = 1;
    const DEFECT_MISSED = 2;
    const DEFECT_INCORRECT_DECISION = 3;

    const SCORE_DISREGARD_POINTS = 0;
    const SCORE_OVERRULED_POINTS = 0;
    const SCORE_OBVIOUSLY_WRONG_POINTS = 5;
    const SCORE_SIGNIFICANTLY_WRONG_POINTS = 10;
    const SCORE_NOT_DEFECT_POINTS = 20;
    const SCORE_DEFECT_MISSED_POINTS = 20;
    const SCORE_NOT_TESTABLE_POINTS = 29;
    const SCORE_DAMAGE_MISSED_POINTS = 30;
    const SCORE_RISK_INJURY_MISSED_POINTS = 40;

    const SCORE_DISREGARD_VALUE = 1;
    const SCORE_OVERRULED_VALUE = 2;
    const SCORE_OBVIOUSLY_WRONG_VALUE = 3;
    const SCORE_SIGNIFICANTLY_WRONG_VALUE = 4;
    const SCORE_NOT_DEFECT = 5;
    const SCORE_DEFECT_MISSED_VALUE = 6;
    const SCORE_NOT_TESTABLE_VALUE = 7;
    const SCORE_DAMAGE_MISSED_VALUE = 8;
    const SCORE_RISK_INJURY_MISSED_VALUE = 9;

    const CASE_OUTCOME_NO_FURTHER_ACTION = 1;
    const CASE_OUTCOME_ADVISORY_WARNING_LETTER = 2;
    const CASE_OUTCOME_DISCIPLINARY_ACTION_REPORT = 3;

    /**
     * @var null
     */
    protected $rfrId = null;

    /**
     * @var null
     */
    protected $mappedRfrId = null;

    /**
     * @var null
     */
    protected $values = null;

    /**
     * @var null
     */
    protected $error = null;

    public function __construct($mappedRfrId, $values)
    {
        $this->mappedRfrId = $mappedRfrId;
        $this->values = $values;
        $this->rfrId = isset($values['rfrId']) ? $values['rfrId'] : null;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return false;
    }

    public static function getClass4RfrsNotTested()
    {
        return [970, 972, 8566, 8567, 8568, 8569];
    }

    /**
     * @param null $error
     *
     * @return BaseValidator
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * @param null $rfrId
     *
     * @return BaseValidator
     */
    public function setRfrId($rfrId)
    {
        $this->rfrId = $rfrId;

        return $this;
    }

    public function getRfrId()
    {
        return $this->rfrId;
    }

    /**
     * @param null $mappedRfrId
     *
     * @return BaseValidator
     */
    public function setMappedRfrId($mappedRfrId)
    {
        $this->mappedRfrId = $mappedRfrId;

        return $this;
    }

    public function getMappedRfrId()
    {
        return $this->mappedRfrId;
    }

    /**
     * @param null $values
     *
     * @return BaseValidator
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }
}
