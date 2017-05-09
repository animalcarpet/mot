<?php

namespace Site\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Generates radio buttons and submit button for roles.
 */
class VehicleTestingStationFormFieldSet extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name = null, $options = null)
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $this->add(
            [
                'name' => 'siteName',
                'type' => 'text',
                'id' => 'siteName',
                'options' => [
                    'label' => 'VTS Trading As Name:',
                ],
                'attributes' => [
                    'id' => 'siteName',
                    'required' => true,
                    'class' => 'form-control',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'csrf',
                'type' => 'Zend\Form\Element\Csrf',
            ]
        );
    }

    public function getInputFilterSpecification()
    {
        return [
            [
                'name' => 'siteName',
                'required' => 'true',
                'validators' => [
                    [
                        'name' => 'string_length',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 100,
                            'messages' => [
                                \Zend\Validator\StringLength::TOO_LONG => 'Site name should be 100 characters or less',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
