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
 * (Account registration) Your email step input filter.
 *
 * Class EmailInputFilter
 */
class EmailInputFilter extends InputFilter
{
    const LIMIT_EMAIL_MAX = 255;

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
        $this->initValidatorEmail();
        $this->initValidatorEmailConfirm();
    }

    /**
     * Adding validators for the email address field/input.
     */
    private function initValidatorEmail()
    {
        $this->add(
            [
                'name'       => self::FIELD_EMAIL,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => EmailAddressValidator::class,
                        'options' => [
                            'allow'   => Hostname::ALLOW_ALL,
                            'message' => self::MSG_EMAIL_INVALID,
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max'     => self::LIMIT_EMAIL_MAX,
                            'message' => sprintf(self::MSG_EMAIL_MAX, self::LIMIT_EMAIL_MAX),
                        ],
                    ],
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_EMAIL_INVALID,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Adding validators for the email address confirmation field/input.
     */
    private function initValidatorEmailConfirm()
    {
        $this->add(
            [
                'name'       => self::FIELD_EMAIL_CONFIRM,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => Identical::class,
                        'options' => [
                            'token'   => self::FIELD_EMAIL,
                            'message' => self::MSG_EMAIL_CONFIRM_DIFFER,
                        ],
                    ],
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_EMAIL_CONFIRM_EMPTY,
                        ],
                    ],
                ],
            ]
        );
    }
}
