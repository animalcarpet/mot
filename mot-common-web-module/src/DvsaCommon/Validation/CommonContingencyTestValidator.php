<?php
/**
 * This file is part of the DVSA MOT Common project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\Validation;

use DateTime;
use DateTimeZone;
use DvsaCommon\Enum\EmergencyReasonCode;
use Zend\Validator\Callback;
use Zend\Validator\Date;
use Zend\Validator\Digits;
use Zend\Validator\StringLength;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorInterface;

/**
 * ContingencyTest Validator.
 */
class CommonContingencyTestValidator implements GroupValidator
{
    const FIELDSET_SITE                       = 'site';
    const FIELDSET_DATE                       = 'date';
    const FIELDSET_TIME                       = 'time';
    const FIELDSET_REASON                     = 'reason';
    const FIELDSET_OTHER_REASON_TEXT          = 'otherReasonText';
    const FIELDSET_CONTINGENCY_CODE           = 'contingencyCode';
    const MESSAGE_DATE_NOT_IN_THE_FUTURE      = 'must not be in the future';
    const MESSAGE_MUST_CHOOSE_A_SITE          = 'you must choose a site';
    const MESSAGE_MUST_ENTER_A_DATE           = 'you must enter a date';
    const MESSAGE_MUST_BE_VALID_DATE          = 'must be a valid date';
    const MESSAGE_MUST_BE_LESS_THAN_3_MONTHS  = 'must be less than 3 months ago';
    const MESSAGE_MUST_CHOOSE_A_REASON        = 'you must choose a reason';
    const MESSAGE_MUST_ENTER_A_TIME           = 'you must enter a time';
    const MESSAGE_MUST_BE_VALID_TIME          = 'must be a valid time';
    const MESSAGE_MUST_ENTER_A_REASON         = 'you must enter a reason';
    const MESSAGE_MUST_BE_LONGER_THAN_5_CHARS = 'must be longer than 5 characters';
    const MESSAGE_MUST_ENTER_CONTINGENCY_CODE = 'you must enter a contingency code';

    /**
     * @var ValidatorInterface[]
     */
    protected $validators;

    /** @var  bool */
    protected $isInfinityContingencyOn;

    /**
     * CommonContingencyTestValidator constructor.
     *
     * @param bool                 $isInfinityContingencyOn
     * @param ValidatorInterface[] $validators
     */
    public function __construct($isInfinityContingencyOn = false, array $validators = [])
    {
        $this->isInfinityContingencyOn = $isInfinityContingencyOn;
        $validators = array_merge($this->getDefaultValidators(), $validators);

        foreach ($validators as $name => $validator) {
            $this->addValidator($name, $validator);
        }
    }

    /**
     * @param string             $name
     * @param ValidatorInterface $validator
     */
    public function addValidator($name, ValidatorInterface $validator)
    {
        $this->validators[$name] = $validator;
    }

    /**
     * @param $name
     *
     * @return null|ValidatorInterface
     */
    public function getValidator($name)
    {
        return isset($this->validators[$name]) ? $this->validators[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data)
    {
        $messages = [];
        $data = $this->processData($data);

        $isTimeValid = true;
        foreach ($this->validators as $name => $validator) {
            if (true === $validator->isValid($data)) {
                continue;
            }

            $messages[$name] = $validator->getMessages();

            if (self::FIELDSET_TIME === $name) {
                $isTimeValid = false;
            }
        }

        // Remove date failed validation messages that are dependent on the time when the time itself isn't valid
        if (false === $isTimeValid && isset($messages[self::FIELDSET_DATE])) {
            foreach (array_keys($messages[self::FIELDSET_DATE]) as $k) {
                if ($messages[self::FIELDSET_DATE][$k] === self::MESSAGE_DATE_NOT_IN_THE_FUTURE ||
                    $messages[self::FIELDSET_DATE][$k] === self::MESSAGE_MUST_BE_LESS_THAN_3_MONTHS) {
                    unset($messages[self::FIELDSET_DATE][$k]);
                }
            }

            if (empty($messages[self::FIELDSET_DATE])) {
                unset($messages[self::FIELDSET_DATE]);
            }
        }

        $valid = empty($messages);

        return new ValidationResult($valid, $messages);
    }

    /**
     * @return ValidatorInterface[]
     */
    protected function getDefaultValidators()
    {
        return [
            self::FIELDSET_SITE              => $this->getSiteValidator(),
            self::FIELDSET_DATE              => $this->getDateValidator(),
            self::FIELDSET_TIME              => $this->getTimeValidator(),
            self::FIELDSET_REASON            => $this->getReasonValidator(),
            self::FIELDSET_OTHER_REASON_TEXT => $this->getOtherReasonTextValidator(),
            self::FIELDSET_CONTINGENCY_CODE  => $this->getContingencyCodeValidator(),
        ];
    }

    /**
     * @return ValidatorChain
     */
    protected function getSiteValidator()
    {
        /*
         * "you must choose a site"
         */
        $site = new Callback(function ($data) {
            if (!isset($data['siteId'])) {
                return false;
            }

            return (new StringLength(['min' => 1]))->isValid($data['siteId']) && (new Digits())->isValid($data['siteId']);
        });
        $site->setMessage(self::MESSAGE_MUST_CHOOSE_A_SITE);

        $validatorChain = new ValidatorChain();
        $validatorChain->attach($site, true);

        return $validatorChain;
    }

    /**
     * @return ValidatorInterface
     */
    protected function getDateValidator()
    {
        /*
         * "you must enter a date"
         */
        $dateMustExist = new Callback(function ($data) {
            if (!isset($data['performedAtYear']) || !isset($data['performedAtMonth']) || !isset($data['performedAtDay'])) {
                return false;
            }

            $stringLengthValidator = new StringLength(['min' => 1]);

            return $stringLengthValidator->isValid($data['performedAtYear'])
                && $stringLengthValidator->isValid($data['performedAtMonth'])
                && $stringLengthValidator->isValid($data['performedAtDay']);
        });
        $dateMustExist->setMessage(self::MESSAGE_MUST_ENTER_A_DATE);

        /*
         * "must be a valid date"
         */
        $validDate = new Callback(function ($data) {
            if (false === (new StringLength(['min' => 4, 'max' => 4]))->isValid($data['performedAtYear'])) {
                return false;
            }

            if (false === (new StringLength(['min' => 2, 'max' => 2]))->isValid($data['performedAtMonth'])) {
                return false;
            }

            if (false === (new StringLength(['min' => 2, 'max' => 2]))->isValid($data['performedAtDay'])) {
                return false;
            }

            return (new Date(['format' => 'Y-m-d']))->isValid(sprintf('%s-%s-%s',
                $data['performedAtYear'], $data['performedAtMonth'], $data['performedAtDay']));
        });
        $validDate->setMessage(self::MESSAGE_MUST_BE_VALID_DATE);

        /*
         * "must be less than 3 months ago"
         */
        $lessThanThreeMonths = new Callback(function ($data) {
            $threeMonthsAgo = new DateTime('-3 months');
            $testDatetime = DateTime::createFromFormat('Y-m-d g:ia', sprintf('%s-%s-%s %s:%s%s',
                $data['performedAtYear'], $data['performedAtMonth'], $data['performedAtDay'],
                $data['performedAtHour'], $data['performedAtMinute'], $data['performedAtAmPm']));

            if (!$testDatetime) {
                return false;
            }

            return $testDatetime->getTimestamp() > $threeMonthsAgo->getTimestamp();
        });
        $lessThanThreeMonths->setMessage(self::MESSAGE_MUST_BE_LESS_THAN_3_MONTHS);

        /*
         * "must not be in the future"
         */
        $notInTheFuture = new Callback(function ($data) {
            $now = new DateTime();
            $testDatetime = DateTime::createFromFormat('Y-m-d g:ia', sprintf('%s-%s-%s %s:%s%s',
                $data['performedAtYear'], $data['performedAtMonth'], $data['performedAtDay'],
                $data['performedAtHour'], $data['performedAtMinute'], $data['performedAtAmPm']), new DateTimeZone("Europe/London"));

            if (!$testDatetime) {
                return false;
            }

            return $testDatetime->getTimestamp() <= $now->getTimestamp();
        });
        $notInTheFuture->setMessage(self::MESSAGE_DATE_NOT_IN_THE_FUTURE);

        $date = new ValidatorChain();
        $date->attach($dateMustExist, true);
        $date->attach($validDate, true);


        if (!$this->isInfinityContingencyOn) {
            $date->attach($lessThanThreeMonths, true);
        }
        $date->attach($notInTheFuture, true);

        return $date;
    }

    /**
     * @return ValidatorInterface
     */
    protected function getTimeValidator()
    {
        /*
         * "you must enter a time"
         */
        $timeMustExist = new Callback(function ($data) {
            if (!isset($data['performedAtHour']) || !isset($data['performedAtMinute']) || !isset($data['performedAtAmPm'])) {
                return false;
            }

            $stringLengthValidator = new StringLength(['min' => 1]);

            return $stringLengthValidator->isValid($data['performedAtHour'])
            && $stringLengthValidator->isValid($data['performedAtMinute'])
            && $stringLengthValidator->isValid($data['performedAtAmPm']);
        });
        $timeMustExist->setMessage(self::MESSAGE_MUST_ENTER_A_TIME);

        /*
         * "must be a valid time"
         */
        $validTime = new Callback(function ($data) {
            if ((false === (new StringLength(['min' => 1, 'max' => 2]))->isValid($data['performedAtHour'])) ||
                0 == intval($data['performedAtHour'])) {
                return false;
            }

            if (false === (new StringLength(['min' => 2, 'max' => 2]))->isValid($data['performedAtMinute'])) {
                return false;
            }

            if (!isset($data['performedAtAmPm']) || !in_array($data['performedAtAmPm'], ['am', 'pm'])) {
                return false;
            }

            return (new Date(['format' => 'g:ia']))->isValid(sprintf('%s:%s%s',
                $data['performedAtHour'], $data['performedAtMinute'], $data['performedAtAmPm']));

        });
        $validTime->setMessage(self::MESSAGE_MUST_BE_VALID_TIME);

        $time = new ValidatorChain();
        $time->attach($timeMustExist, true);
        $time->attach($validTime, true);

        return $time;
    }

    /**
     * @return ValidatorInterface
     */
    protected function getReasonValidator()
    {
        /*
         * "you must choose a reason"
         */
        $reason = new Callback(function ($data) {
            if (!isset($data['reasonCode'])) {
                return false;
            }

            if (false === (new StringLength(['min' => 1]))->isValid($data['reasonCode'])) {
                return false;
            }

            if (!in_array($data['reasonCode'], [EmergencyReasonCode::SYSTEM_OUTAGE,
                EmergencyReasonCode::COMMUNICATION_PROBLEM, EmergencyReasonCode::OTHER, ])) {
                return false;
            }

            return true;
        });
        $reason->setMessage(self::MESSAGE_MUST_CHOOSE_A_REASON);

        return $reason;
    }

    /**
     * @return ValidatorInterface
     */
    protected function getOtherReasonTextValidator()
    {
        /*
         * "you must enter a reason"
         */
        $notEmptyCallback = new Callback(function ($data) {
            if (!isset($data['reasonCode']) || $data['reasonCode'] !== EmergencyReasonCode::OTHER) {
                return true;
            }

            if (!isset($data['otherReasonText']) || false === (new StringLength(['min' => 1]))->isValid($data['otherReasonText'])) {
                return false;
            }

            return true;
        });
        $notEmptyCallback->setMessage(self::MESSAGE_MUST_ENTER_A_REASON);

        /*
         * "must be longer than 5 characters"
         */
        $minLengthCallback = new Callback(function ($data) {
            if (!isset($data['reasonCode']) || $data['reasonCode'] !== EmergencyReasonCode::OTHER) {
                return true;
            }

            if (!isset($data['otherReasonText']) || false === (new StringLength(['min' => 6]))->isValid($data['otherReasonText'])) {
                return false;
            }

            return true;
        });
        $minLengthCallback->setMessage(self::MESSAGE_MUST_BE_LONGER_THAN_5_CHARS);

        $otherReasonText = new ValidatorChain();
        $otherReasonText->attach($notEmptyCallback, true);
        $otherReasonText->attach($minLengthCallback, true);

        return $otherReasonText;
    }

    /**
     * @return ValidatorChain
     */
    protected function getContingencyCodeValidator()
    {
        /*
         * "you must enter a contingency code"
         */
        $notEmpty = new Callback(function ($data) {
            if (!isset($data['contingencyCode'])) {
                return false;
            }

            return (new StringLength(['min' => 1]))->isValid($data['contingencyCode']);
        });
        $notEmpty->setMessage(self::MESSAGE_MUST_ENTER_CONTINGENCY_CODE);

        $contingencyCode = new ValidatorChain();
        $contingencyCode->attach($notEmpty, true);

        return $contingencyCode;
    }

    /**
     * Converts underscore_keys to camelCaseKeys.
     *
     * @param array $data
     *
     * @return array
     */
    private function processData(array $data)
    {
        $processedData = [];
        $encoding = mb_internal_encoding();

        foreach (array_keys($data) as $k) {
            $normalisedKey = preg_replace('/^[-_]+/', '', trim(lcfirst($k)));

            $normalisedKey = preg_replace_callback(
                '/[-_\s]+(.)?/u',
                function ($match) use ($encoding) {
                    if (isset($match[1])) {
                        return mb_strtoupper($match[1], $encoding);
                    } else {
                        return '';
                    }
                },
                $normalisedKey
            );

            $normalisedKey = preg_replace_callback(
                '/[\d]+(.)?/u',
                function ($match) use ($encoding) {
                    return mb_strtoupper($match[0], $encoding);
                },
                $normalisedKey
            );

            $processedData[$normalisedKey] = $data[$k];
        }

        return $processedData;
    }
}
