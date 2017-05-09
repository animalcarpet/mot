<?php

namespace PersonApi\Service\Validator\MotTestingCertificate;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateTimeApiFormat;
use Zend\Validator\Date;
use Zend\Validator\AbstractValidator;

class DateOfQualificationValidator extends AbstractValidator
{
    const MSG_IS_FUTURE_DATE = 'msgIsFutureDate';
    const MSG_INVALID_DATE_FORMAT = 'msgInvalidDateFormat';
    const MSG_IS_EMPTY = 'msgIsEmpty';

    const ERROR_IS_FUTURE_DATE = 'must not be in the future';
    const ERROR_INVALID_DATE_FORMAT = 'must be a valid date';
    const ERROR_IS_EMPTY = 'you must enter a date awarded';

    private $dateTimeHolder;

    protected $messageTemplates = [
        self::MSG_IS_FUTURE_DATE => self::ERROR_IS_FUTURE_DATE,
        self::MSG_INVALID_DATE_FORMAT => self::ERROR_INVALID_DATE_FORMAT,
        self::MSG_IS_EMPTY => self::ERROR_IS_EMPTY,
    ];

    public function __construct(DateTimeHolder $dateTimeHolder, $options = null)
    {
        parent::__construct($options);

        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function isValid($value)
    {
        $this->setValue($value);

        if (empty($value)) {
            $this->error(self::MSG_IS_EMPTY);

            return false;
        }

        $dateFormatValidator = new Date();
        $dateFormatValidator->setFormat(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY);

        try {
            $isValidFormat = $dateFormatValidator->isValid($value);
        } catch (\Exception $e) {
            $isValidFormat = false;
        }

        if (!$isValidFormat) {
            $this->error(self::MSG_INVALID_DATE_FORMAT);

            return false;
        }

        $date = new \DateTime($value);
        if ($date > $this->dateTimeHolder->getCurrentDate()) {
            $this->error(self::MSG_IS_FUTURE_DATE);

            return false;
        }

        return true;
    }
}
