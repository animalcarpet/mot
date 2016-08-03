<?php

namespace DvsaCommon\Validator;


use DvsaCommon\Date\DateUtils;
use Zend\Form\Element\DateTimeLocal;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Date;
use Zend\Validator\Exception;
use Zend\Validator\Regex;

class DateInPastValidator extends AbstractValidator
{
    const IS_EMPTY = 'isEmpty';
    const IS_FUTURE	= 'isFuture';
    const IS_INVALID_FORMAT = 'isNotValidFormat';

    const ERR_MSG_IS_EMPTY = 'you must enter a date';
    const ERR_MSG_IS_FUTURE	= 'must not be in the future';
    const ERR_MSG_IS_INVALID_FORMAT = 'must be a valid date';

    protected $messageTemplates = array(
        self::IS_EMPTY          => self::ERR_MSG_IS_EMPTY,
        self::IS_FUTURE         => self::ERR_MSG_IS_FUTURE,
        self::IS_INVALID_FORMAT => self::ERR_MSG_IS_INVALID_FORMAT,
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  String $value  "day-month-year"
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        try {
            if (empty($value)) {
                $this->error(self::IS_EMPTY);
                return false;
            }

            $dateFormatValidator = new Date();
            $dateFormatValidator->setFormat("Y-m-d");
            $isValidFormat = $dateFormatValidator->isValid($value);

            if (!$isValidFormat) {
                $this->error(self::IS_INVALID_FORMAT);
                return false;
            }

            $convertedDate = DateUtils::toUserTz(new \DateTime($value));

            $today = DateUtils::today();

            if ($convertedDate > $today) {
                $this->error(self::IS_FUTURE);
                return false;
            }

        } catch (\Exception $e) {
            $this->error(self::IS_INVALID_FORMAT);
            return false;
        }

        return true;
    }
}
