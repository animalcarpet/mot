<?php

namespace Vehicle\CreateVehicle\Form;

use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class CountryOfRegistrationForm extends Form
{
    const COUNTRY_OF_REGISTRATION_NAME = 'countryOfRegistration';
    const PLEASE_SELECT = 'Please select';

    const PLEASE_SELECT_ERROR_MSG = 'Country of registration - select an option';
    const PLEASE_SELECT_FIELD_ERROR_MSG = 'Select an option';

    private $errorMessages = [];
    private $isErrorOnCountryField = false;

    public function __construct(array $countries, $selectedCountry = null)
    {
        parent::__construct();
        $countries = $this->formatCountriesForDisplay($countries);

        $this->add((new Select())
            ->setName(self::COUNTRY_OF_REGISTRATION_NAME)
            ->setValueOptions($countries)
            ->setValue($selectedCountry)
            ->setOption('label_attributes', ['class' => 'block-label'])
            ->setAttribute('class', 'form-control form-control-1-2')
            ->setAttribute('id', self::COUNTRY_OF_REGISTRATION_NAME)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        $fieldValid = true;

        if ($this->getCountryOfRegistration()->getValue() == self::PLEASE_SELECT) {
            $this->addErrorMessage(self::PLEASE_SELECT_ERROR_MSG);
            $this->addLabelError($this->getCountryOfRegistration(), [self::PLEASE_SELECT_FIELD_ERROR_MSG]);
            $this->isErrorOnCountryField = true;
            $fieldValid = false;
        }

        return $isValid && $fieldValid;
    }

    public function getCountryOfRegistration()
    {
        return $this->get(self::COUNTRY_OF_REGISTRATION_NAME);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function addErrorMessage($message)
    {
        array_push($this->errorMessages, $message);
    }

    public function IsErrorOnCountryField()
    {
        return $this->isErrorOnCountryField;
    }

    public function addLabelError(ElementInterface $field, $errors)
    {
        $field->setMessages($errors);
    }

    private function formatCountriesForDisplay(array $makes)
    {
        $options = [];
        $options [self::PLEASE_SELECT] = self::PLEASE_SELECT;
        foreach ($makes as $make) {
            $options[$make['code']] = $make['name'];
        }

        return $options;
    }
}