<?php

namespace DvsaMotTest\Form\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\IncorrectDateFormatException;
use DvsaCommon\Date\Exception\NonexistentDateException;
use Zend\Validator\AbstractValidator;

/**
 * Class SpecialNoticePublishDateValidator.
 */
class SpecialNoticePublishDateValidator extends AbstractValidator
{
    const ERROR_PAST = 'Date cannot be in the past';
    const ERROR_FORMAT = 'Incorrect date format, dd-mm-yyyy expected';
    const ERROR_INCORRECT = 'Date is incorrect';
    const ERROR_NONEXISTENT = 'Date does not exist';

    protected $messageTemplates = [
        self::ERROR_PAST => self::ERROR_PAST,
        self::ERROR_FORMAT => self::ERROR_FORMAT,
        self::ERROR_INCORRECT => self::ERROR_INCORRECT,
        self::ERROR_NONEXISTENT => self::ERROR_NONEXISTENT,
    ];

    public function isValid($value)
    {
        try {
            $part = explode('-', $value);
            $date = DateUtils::toDateFromParts($part[2], $part[1], $part[0]);
        } catch (IncorrectDateFormatException $e) {
            $this->error(self::ERROR_FORMAT);

            return false;
        } catch (NonexistentDateException $e) {
            $this->error(self::ERROR_NONEXISTENT);

            return false;
        } catch (\Exception $e) {
            $this->error(self::ERROR_INCORRECT);

            return false;
        }

        $today = new \DateTime();

        if (DateUtils::compareDates($today, $date) > 0) {
            $this->error(self::ERROR_PAST);

            return false;
        }

        return true;
    }
}
