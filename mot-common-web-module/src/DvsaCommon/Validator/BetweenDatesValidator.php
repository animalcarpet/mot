<?php

namespace DvsaCommon\Validator;

use DvsaCommon\Date\DateTimeApiFormat;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class BetweenDatesValidator extends AbstractValidator
{
    const FIELD_DAY = 'day';
    const FIELD_MONTH = 'month';
    const FIELD_YEAR  = 'year';

    const IS_INVALID_TYPE = 'isInvalidType';
    const INVALID_DATE_INCLUSIVE = 'invalidDateInclusive';
    const INVALID_DATE = 'invalidDate';


    const ERR_MSG_IS_INVALID_TYPE = "date should be a string or DateTime object";
    const ERR_MSG_INVALID_DATE_INCLUSIVE = "date should be greater or equal than '%minDate%' and less or equal than '%maxDate%'";
    const ERR_MSG_INVALID_DATE = "date should be greater than '%minDate%' and less than '%maxDate%'";


    protected $messageTemplates = [
        self::IS_INVALID_TYPE => self::ERR_MSG_IS_INVALID_TYPE,
        self::INVALID_DATE_INCLUSIVE => self::ERR_MSG_INVALID_DATE_INCLUSIVE,
        self::INVALID_DATE => self::ERR_MSG_INVALID_DATE,
    ];

    protected $messageVariables = [
        'minDate' => 'min',
        'maxDate' => 'max',
    ];

    protected $min;
    protected $max;

    private $minDate;
    private $maxDate;
    private $isInclusive = false;

    public function __construct(\DateTime $minDate, \DateTime $maxDate, $options = null)
    {
        parent::__construct($options);

        if ($minDate >= $maxDate) {
            throw new \InvalidArgumentException("Min date is greater or equal max date");
        }

        $this->minDate = $minDate;
        $this->maxDate = $maxDate;

        $dateFormat = DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY;
        if (is_array($options) && array_keys($options, "dateFormat")) {
            $dateFormat = $options["dateFormat"];
        }

        $this->min = $minDate->format($dateFormat);
        $this->max = $maxDate->format($dateFormat);
    }

    public function isValid($value)
    {
        if ($value instanceof \DateTime) {
            $date = $value;
        } elseif (is_string($value)) {
            $date = new \DateTime($value);
        } else {
            $this->error(self::IS_INVALID_TYPE);
            return false;
        }

        if ($this->isInclusive) {
            if ($date < $this->minDate || $date > $this->maxDate) {
                $this->error(self::INVALID_DATE_INCLUSIVE);
                return false;
            }
        } else {
            if ($date <= $this->minDate || $date >= $this->maxDate) {
                $this->error(self::INVALID_DATE);
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $isInclusive
     */
    public function setInclusive($isInclusive)
    {
        $this->isInclusive = $isInclusive;
    }
}