<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class SecurityCardSerialNumberValidator extends AbstractValidator
{
    const SERIAL_NUMBER_FORMAT = "/^[a-zA-Z]{4}[0-9]{8}$/";

    const MSG_KEY_SERIAL_NUMBER = "Enter a valid serial number";

    /** @var SecurityCardSerialNumberValidationCallback */
    private $validationCallback;

    protected $messageTemplates = [
        self::MSG_KEY_SERIAL_NUMBER => 'Enter a valid serial number',
    ];

    public function isValid($value, $context = null)
    {
        $serialNumber = $value;
        if (!preg_match(self::SERIAL_NUMBER_FORMAT, $serialNumber)) {
            $this->error(self::MSG_KEY_SERIAL_NUMBER);
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
