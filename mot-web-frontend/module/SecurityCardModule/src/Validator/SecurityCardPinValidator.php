<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class SecurityCardPinValidator extends AbstractValidator
{
    /**
     * @var SecurityCardPinValidationCallback $validationCallback
     */
    private $validationCallback;

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
            if ($this->validationCallback) {
                $this->validationCallback->onBlankPin();
            }
            return false;
        }

        if (!is_numeric($pin)) {
            $this->error(self::MSG_KEY_PIN_NUMERIC);
            if ($this->validationCallback) {
                $this->validationCallback->onNonNumeric();
            }
            return false;
        }

        if (strlen($pin) != self::PIN_LENGTH) {
            $this->error(self::MSG_KEY_PIN_LENGTH);
            if ($this->validationCallback) {
                $this->validationCallback->onInvalidLength();
            }
            return false;
        }

        return true;
    }

    /**
     * @param SecurityCardPinValidationCallback $validationCallback
     * @return $this
     */
    public function setValidationCallback(SecurityCardPinValidationCallback $validationCallback = null)
    {
        $this->validationCallback = $validationCallback;
        return $this;
    }
}