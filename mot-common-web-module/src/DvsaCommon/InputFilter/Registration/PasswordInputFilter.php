<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Validator\PasswordValidator;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;

/**
 * (Account registration) Create a password's step input filter.
 *
 * Class PasswordInputFilter
 */
class PasswordInputFilter extends InputFilter
{
    /** Create a password */
    const FIELD_PASSWORD = 'password';
    const MSG_PASSWORD_EMPTY = 'enter a password';
    const MSG_PASSWORD_MATCH_USERNAME = 'password must not match your username';
    const MSG_PASSWORD_HISTORY = 'password was found in the password history';

    /** Re-type your password */
    const FIELD_PASSWORD_CONFIRM = 'passwordConfirm';
    const MSG_PASSWORD_CONFIRM_EMPTY = 're-type your password';
    const MSG_PASSWORD_CONFIRM_DIFFER = 'the passwords you have entered don\'t match';

    private $username = '';

    public function __construct(MotIdentityProviderInterface $identityProvider = null)
    {
        if ($identityProvider) {
            $this->username = $identityProvider->getIdentity()->getUsername();
        }
    }

    public function init()
    {
        $this->initValidatorsForPassword();
        $this->initValidatorsForPasswordConfirmation();
    }

    /**
     * Adding validators for the password field/input.
     */
    private function initValidatorsForPassword()
    {
        $this->add(
            [
                'name'       => self::FIELD_PASSWORD,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_PASSWORD_EMPTY,
                        ],
                    ],
                    [
                        'name' => PasswordValidator::class,
                        'options' => [
                            'username' => $this->username,
                            'messages' => [
                                'msgUsername' => self::MSG_PASSWORD_MATCH_USERNAME
                            ]
                        ]
                    ],
                ],
            ]
        );
    }

    /**
     * Adding validators for the password confirmation field/input.
     */
    private function initValidatorsForPasswordConfirmation()
    {
        $this->add(
            [
                'name'       => self::FIELD_PASSWORD_CONFIRM,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_PASSWORD_CONFIRM_EMPTY,
                        ],
                    ],
                    [
                        'name'    => Identical::class,
                        'options' => [
                            'token'   => self::FIELD_PASSWORD,
                            'message' => self::MSG_PASSWORD_CONFIRM_DIFFER,
                        ],
                    ],
                ],
            ]
        );
    }
}
