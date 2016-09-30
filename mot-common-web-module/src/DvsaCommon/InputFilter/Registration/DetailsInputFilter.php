<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

use DvsaCommon\Validator\EmailAddressValidator;
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

    /** Phone */
    const FIELD_PHONE = 'phone';
    const MSG_PHONE_MAX = 'must be %d characters or less';
    const MSG_PHONE_INVALID = 'enter a telephone number';

    /** Email address */
    const FIELD_EMAIL = 'emailAddress';
    const MSG_EMAIL_MAX = 'must be %d, or less, characters long';
    const MSG_EMAIL_INVALID = 'enter a valid email address';

    /** Email confirmation */
    const FIELD_EMAIL_CONFIRM = 'confirmEmailAddress';
    const MSG_EMAIL_CONFIRM_EMPTY = 'confirm your email address';
    const MSG_EMAIL_CONFIRM_DIFFER = 'the email addresses you have entered don\'t match';

    public function init()
    {
        $this->initValidatorsForNames(self::FIELD_FIRST_NAME, true, self::MSG_FIRST_NAME_EMPTY);
        $this->initValidatorsForNames(self::FIELD_MIDDLE_NAME);
        $this->initValidatorsForNames(self::FIELD_LAST_NAME, true, self::MSG_LAST_NAME_EMPTY);
        $this->initValidatorPhone();
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

    /**
     * Adding validators for the phone field/input.
     */
    private function initValidatorPhone()
    {
        $this->add(
            [
                'name'       => self::FIELD_PHONE,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => TelephoneNumberValidator::class,
                        'options' => [
                            'allow'   => Hostname::ALLOW_ALL,
                            'message' => self::MSG_PHONE_INVALID,
                        ],
                    ],
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_PHONE_INVALID,
                        ],
                    ],
                ],
            ]
        );
    }
}
