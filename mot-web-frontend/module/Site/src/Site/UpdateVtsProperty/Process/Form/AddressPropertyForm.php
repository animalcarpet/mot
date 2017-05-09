<?php

namespace Site\UpdateVtsProperty\Process\Form;

use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class AddressPropertyForm extends Form
{
    const FIELD_ADDRESS_LINE_1 = 'address_line1';
    const FIELD_ADDRESS_LINE_2 = 'address_line2';
    const FIELD_ADDRESS_LINE_3 = 'address_line3';
    const FIELD_TOWN = 'town';
    const FIELD_POSTCODE = 'postcode';
    const MSG_TOO_LONG = 'must be %max% characters or less';

    public function __construct()
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_1)
            ->setLabel('Address')
            ->setAttribute('id', 'vtsAddressLine1')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );
        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_2)
            ->setAttribute('id', 'vtsAddressLine2')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound')
        );
        $this->add((new Text())
            ->setName(self::FIELD_ADDRESS_LINE_3)
            ->setAttribute('id', 'vtsAddressLine3')
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
            ->setName(self::FIELD_POSTCODE)
            ->setLabel('Postcode')
            ->setAttribute('id', 'postcode')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('inputModifier', '1-8')
        );

        $filter = new InputFilter();

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_1,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'you must enter the first line of the address',
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => 50,
                        'messages' => [
                            StringLength::TOO_LONG => self::MSG_TOO_LONG,
                        ],
                    ],
                ],
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_2,
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => 50,
                        'messages' => [
                            StringLength::TOO_LONG => self::MSG_TOO_LONG,
                        ],
                    ],
                ],
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_ADDRESS_LINE_3,
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => 50,
                        'messages' => [
                            StringLength::TOO_LONG => self::MSG_TOO_LONG,
                        ],
                    ],
                ],
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_TOWN,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'you must enter a town or city',
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => 50,
                        'messages' => [
                            StringLength::TOO_LONG => self::MSG_TOO_LONG,
                        ],
                    ],
                ],
            ],
        ]);

        $filter->add([
            'name' => self::FIELD_POSTCODE,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => 'you must enter a postcode',
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => 10,
                        'messages' => [
                            StringLength::TOO_LONG => self::MSG_TOO_LONG,
                        ],
                    ],
                ],
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
