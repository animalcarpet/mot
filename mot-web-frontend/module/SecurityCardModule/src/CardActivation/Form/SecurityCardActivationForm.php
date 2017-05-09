<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidator;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardSerialNumberValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardSerialNumberValidator;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class SecurityCardActivationForm extends Form
{
    const SERIAL_NUMBER = 'serial_number';
    const PIN = 'pin';

    public function __construct(
        SecurityCardPinValidationCallback $pinValidationCallback = null,
        SecurityCardSerialNumberValidationCallback $serialNumberValidationCallback = null
    ) {
        parent::__construct();
        $this->add((new Text())
            ->setName(self::SERIAL_NUMBER)
            ->setLabel('Serial number')
            ->setAttribute('id', self::SERIAL_NUMBER)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-2')
            ->setAttribute('help', 'For example: 1234567898765432')
            ->setAttribute('autoCompleteOff', true)
        );
        $this->add((new Text())
            ->setName(self::PIN)
            ->setLabel('Security card PIN')
            ->setAttribute('id', self::PIN)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-4')
            ->setAttribute('help', 'For example: 123456')
            ->setAttribute('autoCompleteOff', true)
    );

        $filter = new InputFilter();

        $serialNumberInput = new Input(self::SERIAL_NUMBER);
        $serialNumberInput
            ->setRequired(true)
            ->setContinueIfEmpty(true);
        $serialNumberInput->getValidatorChain()
            ->attach((new SecurityCardSerialNumberValidator())->setValidationCallback($serialNumberValidationCallback));
        $filter->add($serialNumberInput);

        $pinInput = new Input(self::PIN);
        $pinInput->setContinueIfEmpty(true);
        $pinInput->getValidatorChain()
            ->attach((new SecurityCardPinValidator())->setValidationCallback($pinValidationCallback));
        $filter->add($pinInput);

        $this->setInputFilter($filter);
    }

    public function clearPin()
    {
        $this->get(self::PIN)->setValue(null);
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError($field, $error)
    {
        $field->setMessages([$error]);
    }

    public function getSerialNumberField()
    {
        return $this->get(self::SERIAL_NUMBER);
    }

    public function getPinField()
    {
        return $this->get(self::PIN);
    }
}
