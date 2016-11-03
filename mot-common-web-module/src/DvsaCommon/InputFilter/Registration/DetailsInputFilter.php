<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

use DvsaCommon\Validator\DateOfBirthValidator;
use DvsaCommon\Validator\TelephoneNumberValidator;
use Zend\InputFilter\InputFilter;
use Zend\Validator\EmailAddress;
use Zend\Validator\Hostname;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

/**
 * (Account registration) Your details' step input filter.
 *
 * Class DetailsInputFilter
 */
class DetailsInputFilter extends InputFilter
{
    /** To be used by firs name, middle name and last name */
    const LIMIT_NAME_MAX = 45;
    const LIMIT_PHONE_MAX = 24;
    const LIMIT_EMAIL_MAX = 255;

    /** First name */
    const FIELD_FIRST_NAME = 'firstName';
    const MSG_FIRST_NAME_EMPTY = 'enter a first name';
    const MSG_NAME_MAX = 'must be %d, or less, characters long'; # common for all the name related fields
    const MSG_NAME_NO_PATTERN_MATCH = 'must only contain letters, spaces, hyphens and apostrophes';

    /** Middle name */
    const FIELD_MIDDLE_NAME = 'middleName';

    /** Last name */
    const FIELD_LAST_NAME = 'lastName';
    const MSG_LAST_NAME_EMPTY = 'enter a last name';

    /** Date of Birth */
    const FIELD_DAY = 'day';
    const FIELD_MONTH = 'month';
    const FIELD_YEAR = 'year';
    const FIELD_DATE = 'date';

    public function init()
    {
        $this->initValidatorsForNames(self::FIELD_FIRST_NAME, true, self::MSG_FIRST_NAME_EMPTY);
        $this->initValidatorsForNames(self::FIELD_MIDDLE_NAME);
        $this->initValidatorsForNames(self::FIELD_LAST_NAME, true, self::MSG_LAST_NAME_EMPTY);
        $this->initValidatorDateOfBirth();
    }

    /**
     * Adding validators for the first name, middle name and last name's field/input.
     *
     * @param string     $fieldName  @see self::FIELD_*_NAME
     * @param bool|false $isRequired
     * @param string     $message    (optional) @see self::MSG_*_NAME_EMPTY
     */
    private function initValidatorsForNames($fieldName, $isRequired = false, $message = null)
    {
        $input = [
            'name' => $fieldName,
            'required' => $isRequired,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => self::LIMIT_NAME_MAX,
                        'message' => sprintf(self::MSG_NAME_MAX, self::LIMIT_NAME_MAX),
                    ],
                ],
                [
                    'name' => Regex::class,
                    'options' => [
                        'pattern' => "/^\\pL[\\pL \\-']*$/u",
                        'message' => self::MSG_NAME_NO_PATTERN_MATCH,
                    ],
                ],
            ],
        ];

        if ($isRequired) {
            array_unshift($input['validators'], [
                'name'    => NotEmpty::class,
                'options' => ['message' => $message]
            ]);
        }

        $this->add($input);
    }

    private function initValidatorDateOfBirth()
    {
        $this->add(
            [
                'name' => self::FIELD_DATE,
                'required' => true,
                'validators' => [
                    [
                        'name' => DateOfBirthValidator::class,
                    ],
                ],
            ]
        );
    }
}
