<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class PasswordValidator extends AbstractValidator
{
    const MSG_KEY_PASSWORD_BLANK = "passwordBlank";

    protected $messageTemplates = [
        self::MSG_KEY_PASSWORD_BLANK => 'Enter your password',
    ];

    public function isValid($value, $context = null)
    {
        $pin = $value;

        if (strlen($pin) == 0) {
            $this->error(self::MSG_KEY_PASSWORD_BLANK);
            return false;
        }

        return true;
    }
}