<?php
namespace DvsaMotTest\NewVehicle\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class CreateVehicleStepThreeForm extends Form
{
    const FIELD_NAME_OTP = 'oneTimePassword';
    const VALIDATION_MESSAGE_INCORRECT_OTP = 'The PIN you have entered is incorrect';
    const VALIDATION_MESSAGE_EMPTY_OTP = 'PIN is required';

    public function __construct($name = null)
    {
        parent::__construct('VehicleStepThree');

        $elementOTP = new Element\Password(self::FIELD_NAME_OTP);
        $elementOTP->setAttributes(
            [
                'id', self::FIELD_NAME_OTP,
                'class', 'form-control',
                'required' => 'required',
            ]
        );


        $this->add($elementOTP);


        $if = new InputFilter();
        $if->add(
            [
                'name' => self::FIELD_NAME_OTP,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => self::VALIDATION_MESSAGE_EMPTY_OTP,
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->setInputFilter($if);
    }
}
