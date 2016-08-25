<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidator;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardSerialNumberValidator;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class SecurityCardActivationForm extends Form
{

    const SERIAL_NUMBER = 'serial_number';
    const PIN = 'pin';

    public function __construct()
    {
        parent::__construct();
        $this->add((new Text())
            ->setName(self::SERIAL_NUMBER)
            ->setLabel('Serial number')
            ->setAttribute('id', self::SERIAL_NUMBER)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-4')
            ->setAttribute('help', 'For example: STTA12345678')
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


        $filter->add([
            'name' => self::SERIAL_NUMBER,
            'required' => true,
            'validators' => [
                [
                    'name' => SecurityCardSerialNumberValidator::class
                ]
            ],
            'continue_if_empty' => true,
            'allow_empty' => true
        ]);

        $filter->add([
            'name' => self::PIN,
            'required' => false,
            'validators' => [
                [
                    'name' => SecurityCardPinValidator::class
                ]
            ],
            'continue_if_empty' => true,
            'allow_empty' => true
        ]);


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