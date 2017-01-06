<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class SecurityCardSerialNumberValidator extends AbstractValidator
{
    const MSG_KEY_SN_BLANK = "snBlank";

    const MSG_KEY_SN_LENGTH = "snLength";

    const MAX_SERIAL_NUMBER_LENGTH = 16;


    /** @var SecurityCardSerialNumberValidationCallback */
    private $validationCallback;

    protected $messageTemplates = [
        self::MSG_KEY_SN_BLANK => 'Enter a serial number',
        self::MSG_KEY_SN_LENGTH => 'must be less than or equal to 16 characters'
    ];

    public function isValid($value, $context = null)
    {
        $serialNumber = $value;
        if (strlen($serialNumber) == 0) {
            $this->error(self::MSG_KEY_SN_BLANK);
            if ($this->validationCallback) {
                $this->validationCallback->onInvalidFormat();
            }
            return false;
        }

        if (strlen($serialNumber) > self::MAX_SERIAL_NUMBER_LENGTH) {
            $this->error(self::MSG_KEY_SN_LENGTH);
            if ($this->validationCallback) {
                $this->validationCallback->onInvalidFormat();
            }
            return false;
        }

        return true;
    }

    /**
     * @param SecurityCardSerialNumberValidationCallback $validationCallback
     * @return $this
     */
    public function setValidationCallback(SecurityCardSerialNumberValidationCallback $validationCallback = null)
    {
        $this->validationCallback = $validationCallback;

        return $this;
    }
}
