<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Validator;

use DvsaCommon\Validator\EmailAddressValidator;
use Zend\Validator\AbstractValidator;

class UsernameValidator extends AbstractValidator
{
    const MSG_KEY_USERNAME_BLANK = 'usernameBlank';
    const MSG_KEY_USERNAME_EMAIL = 'usernameEmail';

    protected $messageTemplates = [
        self::MSG_KEY_USERNAME_BLANK => 'Enter your User ID',
        self::MSG_KEY_USERNAME_EMAIL => 'Enter a valid User ID. For example: SMIT1234',
    ];

    public function isValid($value, $context = null)
    {
        $pin = $value;

        if (strlen($pin) == 0) {
            $this->error(self::MSG_KEY_USERNAME_BLANK);

            return false;
        }

        if ((new EmailAddressValidator())->isValid($value)) {
            $this->error(self::MSG_KEY_USERNAME_EMAIL);

            return false;
        }

        return true;
    }
}
