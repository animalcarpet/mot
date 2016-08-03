<?php

namespace DvsaCommon\InputFilter\Account;

use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;


class ChangePasswordInputFilter extends PasswordInputFilter
{
    const FIELD_OLD_PASSWORD = 'oldPassword';
    const MSG_OLD_PASSWORD_EMPTY = 'enter your current password';
    const MSG_PASSWORD_INVALID = "enter a valid password";

    public function init()
    {
        parent::init();
        $this->initValidatorsForOldPassword();
    }

    private function initValidatorsForOldPassword()
    {
        $this->add(
            [
                'name'       => self::FIELD_OLD_PASSWORD,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_OLD_PASSWORD_EMPTY,
                        ],
                    ],
                ],
            ]
        );
    }
}
