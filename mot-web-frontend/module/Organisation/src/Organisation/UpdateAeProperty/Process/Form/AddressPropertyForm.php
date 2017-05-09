<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddressPropertyForm extends Form
{
    const FIELD_ADDRESS_LINE_1 = 'address-line1';
    const FIELD_ADDRESS_LINE_2 = 'address-line2';
    const FIELD_ADDRESS_LINE_3 = 'address-line3';
    const FIELD_COUNTRY = 'country';
    const FIELD_TOWN = 'town';
    const FIELD_POSTCODE = 'postcode';
    const MSG_TOO_LONG = 'must be %max% characters or less';
    const MSG_TOWN_EMPTY = 'you must enter a town or city';
    const MSG_POSTCODE_EMPTY = 'you must enter a postcode';

    const FIELD_MAX_LENGHT = 50;
    const FIELD_POSTCODE_MAX_LENGHT = 10;

    public function __construct()
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_1)
            ->setLabel('Address')
            ->setAttribute('id', 'aeAddressLine1')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );
        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_2)
            ->setAttribute('id', 'aeAddressLine2')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );
        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_3)
            ->setAttribute('id', 'aeAddressLine3')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );
        $this->add((new Text())
            ->setName(self::FIELD_TOWN)
            ->setLabel('Town or city')
            ->setAttribute('id', 'town')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );
        $this->add((new Text())
            ->setName(self::FIELD_COUNTRY)
            ->setLabel('Country (optional)')
            ->setAttribute('id', 'country')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
        );
        $this->add((new Text())
            ->setName(self::FIELD_POSTCODE)
            ->setLabel('Postcode')
            ->setAttribute('id', 'postcode')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('inputModifier', '1-8')
        );

        $filter = new InputFilter();

        $stringLenghtValidator = function ($lenght) {
            return [
                'name' => StringLength::class,
                'options' => [
                    'max' => $lenght,
                    'messages' => [
                        StringLength::TOO_LONG => self::MSG_TOO_LONG,
                    ],
                ],
            ];
        };

        $stringNotEmptyValidator = function ($message) {
            return [
                'name' => NotEmpty::class,
                'options' => [
                    'messages' => [
                        NotEmpty::IS_EMPTY => $message,
                    ],
                ],
            ];
        };

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_1,
            'required' => true,
            'validators' => [
                $stringNotEmptyValidator('you must enter the first line of the address'),
                $stringLenghtValidator(static::FIELD_MAX_LENGHT),
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_2,
            'required' => false,
            'validators' => [
                $stringLenghtValidator(static::FIELD_MAX_LENGHT),
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_3,
            'required' => false,
            'validators' => [
                $stringLenghtValidator(static::FIELD_MAX_LENGHT),
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_TOWN,
            'required' => true,
            'validators' => [
                $stringNotEmptyValidator(self::MSG_TOWN_EMPTY),
                $stringLenghtValidator(static::FIELD_MAX_LENGHT),
            ],
        ]);
        $filter->add([
            'name' => self::FIELD_COUNTRY,
            'required' => false,
            'validators' => [
                $stringLenghtValidator(static::FIELD_MAX_LENGHT),
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_POSTCODE,
            'required' => true,
            'validators' => [
                $stringNotEmptyValidator(self::MSG_POSTCODE_EMPTY),
                $stringLenghtValidator(static::FIELD_POSTCODE_MAX_LENGHT),
            ],
        ]);

        $this->setInputFilter($filter);
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        if (!$isValid) {
            $this->showLabelOnError(self::FIELD_ADDRESS_LINE_2, 'Address Line 2');
            $this->showLabelOnError(self::FIELD_ADDRESS_LINE_3, 'Address Line 3');
        }

        return $isValid;
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}
