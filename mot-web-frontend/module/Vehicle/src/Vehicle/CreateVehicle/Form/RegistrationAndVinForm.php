<?php

namespace Vehicle\CreateVehicle\Form;

use Zend\Form\Element\Checkbox;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;
use Zend\Validator\Regex;

class RegistrationAndVinForm extends Form
{
    const REGISTRATION_FIELD = 'reg-input';
    const VIN_FIELD = 'vin-input';
    const REGISTRATION_CHECKBOX = 'leavingRegBlank';
    const VIN_CHECKBOX = 'leavingVINBlank';
    const ERROR_EMPTY_REGISTRATION = 'Enter the registration or select ‘I can’t provide the registration mark’';
    const ERROR_EMPTY_VIN = 'Enter the VIN or select ‘I can’t provide the VIN’';
    const ERROR_MUST_CONTAIN_ONE_ALPHANUMERIC = 'Must contain at least one number or letter';
    const ERROR_NON_ALLOWED_VALUES = 'Can only contain numbers, letters, spaces, hyphens and full stops';
    const ERROR_REGISTRATION_GREATER_THAN_THIRTEEN_CHARACTERS = 'Must be shorter than 14 characters';
    const ERROR_NOTHING_ENTERED = 'You must enter the registration mark or the VIN';
    const ERROR_CANNOT_SELECT_AND_ENTER_REGISTRATION = 'Either enter the registration or select ‘I can’t provide the registration mark’';
    const ERROR_CANNOT_SELECT_AND_ENTER_VIN = 'Either enter the VIN or select ‘I can’t provide the VIN’';
    const ERROR_VIN_GREATER_THAN_20_CHARACTERS = 'Must be shorter than 21 characters';

    private $errorMessages = [];

    /**
     * RegistrationAndVinForm constructor.
     * @param string $registrationFieldValue
     * @param bool $registrationCheckboxValue
     * @param string $vinFieldValue
     * @param bool $vinCheckboxValue
     */
    public function __construct(
        $registrationFieldValue = null,
        $registrationCheckboxValue = false,
        $vinFieldValue = null,
        $vinCheckboxValue = false
    )
    {
        parent::__construct();

        $this->add((new Text())
            ->setLabel('Registration mark')
            ->setValue($registrationFieldValue)
            ->setName(self::REGISTRATION_FIELD)
            ->setAttribute('id', self::REGISTRATION_FIELD)
            ->setAttribute('required', true)
            ->setAttribute('class', 'form-control')
        );

        $this->add((new Checkbox())
            ->setLabel(self::REGISTRATION_CHECKBOX)
            ->setName(self::REGISTRATION_CHECKBOX)
            ->setAttribute('id', self::REGISTRATION_CHECKBOX)
            ->setAttribute('checked', $registrationCheckboxValue)
        );

        $this->add((new Text())
            ->setLabel('VIN')
            ->setValue($vinFieldValue)
            ->setName(self::VIN_FIELD)
            ->setAttribute('id', self::VIN_FIELD)
            ->setAttribute('required', true)
            ->setAttribute('class', 'form-control')
        );

        $this->add((new Checkbox())
            ->setLabel(self::VIN_CHECKBOX)
            ->setName(self::VIN_CHECKBOX)
            ->setAttribute('id', self::VIN_CHECKBOX)
            ->setAttribute('checked', $vinCheckboxValue)
        );
    }

    public function isValid()
    {
        $isValid = true;
        $regCheckboxValue = $this->getRegistrationCheckbox()->getAttributes()['checked'];
        $vinCheckboxValue = $this->getVINCheckbox()->getAttributes()['checked'];
        $registrationFieldValue = $this->getRegistrationField()->getValue();
        $vinFieldValue = $this->getVINField()->getValue();
        $emptyRegistrationField = !strlen($registrationFieldValue) > 0;
        $emptyVinField = !strlen($vinFieldValue) > 0;

        if ($regCheckboxValue && $vinCheckboxValue && $emptyRegistrationField && $emptyVinField) {
            $this->addErrorMessage('You must enter the registration mark or the VIN');
            return false;
        }

        if (!$this->isEmptyRegistrationField()
            || !$this->isRegistrationFieldAndCheckboxSelected()
            || !$this->isRegistrationFieldGreaterThan13()
            || !$this->containsOneLetterOrNumber()
            || !$this->onlyContainsNumbersLettersFullStopsAndHyphens()
        ) {
            $isValid = false;
        }

        if (!$this->isEmptyVinField()
            || !$this->isVinFieldAndCheckboxSelected()
            || !$this->isVinFieldGreaterThan20()
        ) {
            $isValid = false;
        }

        return $isValid;
    }

    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    public function getVINField()
    {
        return $this->get(self::VIN_FIELD);
    }

    public function getVINCheckbox()
    {
        return $this->get(self::VIN_CHECKBOX);
    }

    public function getRegistrationField()
    {
        return $this->get(self::REGISTRATION_FIELD);
    }

    public function getRegistrationCheckbox()
    {
        return $this->get(self::REGISTRATION_CHECKBOX);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    private function isVinFieldAndCheckboxSelected()
    {
        $isValid = true;

        if ($this->getVINCheckbox()->getAttributes()['checked'] && (strlen($this->getVINField()->getValue()) > 0)) {
            $this->addErrorMessage('VIN - either enter the VIN or select ‘I can’t provide the VIN’');
            $this->setCustomError($this->getVINField(), self::ERROR_CANNOT_SELECT_AND_ENTER_VIN);
            $this->setLabelOnError(self::VIN_FIELD, 'VIN');
            $isValid = false;
        }

        return $isValid;
    }

    private function isRegistrationFieldAndCheckboxSelected()
    {
        $isValid = true;

        if ($this->getRegistrationCheckbox()->getAttributes()['checked']
            && (strlen($this->getRegistrationField()->getValue()) > 0)) {
            $this->addErrorMessage('Registration mark - either enter the registration or select ‘I can’t provide the registration mark’');
            $this->setCustomError($this->getRegistrationField(), self::ERROR_CANNOT_SELECT_AND_ENTER_REGISTRATION);
            $this->setLabelOnError(self::REGISTRATION_FIELD, 'Registration mark');
            $isValid = false;
        }

        return $isValid;
    }

    private function onlyContainsNumbersLettersFullStopsAndHyphens()
    {
        $isValid = true;

        $onlyNumbersLettersSpacesHyphensFullStopsRegex = new Regex(array('pattern' => '/^[a-zA-Z.\d\s-]{0,13}$/'));

        if ($this->isEmptyRegistrationField()
            && $this->isRegistrationFieldGreaterThan13()
            && $this->containsOneLetterOrNumber()
            && !$onlyNumbersLettersSpacesHyphensFullStopsRegex->isValid($this->getRegistrationField()->getValue())) {
            $this->addErrorMessage('Registration mark - can only contain numbers, letters, spaces, hyphens and full stops');
            $this->setCustomError($this->getRegistrationField(), self::ERROR_NON_ALLOWED_VALUES);
            $this->setLabelOnError(self::REGISTRATION_FIELD, 'Registration mark');
            $isValid = false;
        }

        return $isValid;
    }

    private function containsOneLetterOrNumber()
    {
        $isValid = true;

        $mustContainAtLeastOneLetterOrNumberRegex = new Regex(array('pattern' => '/^(?=.*[a-zA-Z]|.*[\d]).{0,13}$/'));

        if ((strlen($this->getRegistrationField()->getValue()) > 0) &&
            !$mustContainAtLeastOneLetterOrNumberRegex->isValid($this->getRegistrationField()->getValue())) {
            $this->addErrorMessage('Registration mark - must contain at least one number or letter');
            $this->setCustomError($this->getRegistrationField(), self::ERROR_MUST_CONTAIN_ONE_ALPHANUMERIC);
            $this->setLabelOnError(self::REGISTRATION_FIELD, 'Registration mark');
            $isValid = false;
        }

        return $isValid;
    }

    private function isRegistrationFieldGreaterThan13()
    {
        $isValid = true;

        if (strlen($this->getRegistrationField()->getValue()) > 13) {
            $this->addErrorMessage('Registration mark - must be shorter than 14 characters');
            $this->setCustomError($this->getRegistrationField(), self::ERROR_REGISTRATION_GREATER_THAN_THIRTEEN_CHARACTERS);
            $this->setLabelOnError(self::REGISTRATION_FIELD, 'Registration mark');
            $isValid = false;
        }

        return $isValid;
    }

    private function isVinFieldGreaterThan20()
    {
        $isValid = true;

        if (strlen($this->getVINField()->getValue()) > 20) {
            $this->addErrorMessage('VIN - must be shorter than 21 characters');
            $this->setCustomError($this->getVINField(), self::ERROR_VIN_GREATER_THAN_20_CHARACTERS);
            $this->setLabelOnError(self::VIN_FIELD, 'VIN');
            $isValid = false;
        }

        return $isValid;
    }

    private function isEmptyRegistrationField()
    {
        $isValid = true;

        if ((!strlen($this->getRegistrationField()->getValue()) > 0) && !$this->getRegistrationCheckbox()->getAttributes()['checked']) {
            $this->addErrorMessage('Registration mark - enter the registration or select ‘I can’t provide the registration mark’');
            $this->setCustomError($this->getRegistrationField(), self::ERROR_EMPTY_REGISTRATION);
            $this->setLabelOnError(self::REGISTRATION_FIELD, 'Registration mark');
            $isValid = false;
        }

        return $isValid;
    }

    private function isEmptyVinField()
    {
        $isValid = true;

        if (!strlen($this->getVINField()->getValue()) > 0 && !$this->getVINCheckbox()->getAttributes()['checked']) {
            $this->addErrorMessage('VIN - enter the VIN or select ‘I can’t provide the VIN’');
            $this->setCustomError($this->getVINField(), self::ERROR_EMPTY_VIN);
            $this->setLabelOnError(self::VIN_FIELD, 'VIN');
            $isValid = false;
        }

        return $isValid;
    }

    private function addErrorMessage($errorMessage)
    {
        array_push($this->errorMessages, $errorMessage);
    }

    private function setLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}