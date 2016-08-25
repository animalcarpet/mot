<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class SecurityCardPinValidator extends AbstractValidator
{
    const PIN_LENGTH = 6;

    const MSG_KEY_PIN_LENGTH = "pinLength";
    const MSG_KEY_PIN_NUMERIC = "pinNumeric";
    const MSG_KEY_PIN_BLANK = "pinBlank";


    protected $messageTemplates = [
        self::MSG_KEY_PIN_LENGTH => 'Enter a 6 digit number',
        self::MSG_KEY_PIN_BLANK => 'Enter a PIN number',
        self::MSG_KEY_PIN_NUMERIC => 'Enter a valid PIN number'
    ];

    public function isValid($value, $context = null)
    {
        $pin = $value;

        if (strlen($pin) == 0) {
            $this->error(self::MSG_KEY_PIN_BLANK);
            return false;
        }

        if (!is_numeric($pin)) {
            $this->error(self::MSG_KEY_PIN_NUMERIC);
            return false;
        }

        if (strlen($pin) != self::PIN_LENGTH) {
            $this->error(self::MSG_KEY_PIN_LENGTH);
            return false;
        }

        return true;
    }
}