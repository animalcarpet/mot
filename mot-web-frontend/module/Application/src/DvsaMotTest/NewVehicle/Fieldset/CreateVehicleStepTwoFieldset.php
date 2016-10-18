<?php

namespace DvsaMotTest\NewVehicle\Fieldset;

use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaMotTest\Service\AuthorisedClassesService;
use Zend\Form\Fieldset;
use Zend\I18n\Validator\IsInt;
use Zend\InputFilter\InputFilterProviderInterface;
use \Zend\Form\Element\Select;
use Zend\Validator\Between;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class CreateVehicleStepTwoFieldset extends Fieldset implements InputFilterProviderInterface
{
    const LABEL_PLEASE_SELECT = 'Please select';
    const LABEL_OTHER_KEY = 'OTHER';
    const LABEL_OTHER_VALUE = 'Other';

    const LIMIT_MODEL_MAX = 30;
    const LIMIT_CC_MIN = 0.1;
    const LIMIT_CC_MAX = 10000;

    private $authorisedClasses = [];

    public function __construct($name, array $options)
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        if (isset($options['vehicleData']['authorisedClasses'])) {
            $this->authorisedClasses = $options['vehicleData']['authorisedClasses'];
        }

        $this->add(
            [
                'name' => 'model',
                'type' => Select::class,
                'options' => [
                    'label' => 'Model',
                    'value_options' => $this->getDropdownOptionsFromAttributes(
                        $options['vehicleData']['model'],
                        'id',
                        'name',
                        true,
                        true
                    )
                ]
            ]
        );
        $this->add(
            [
                'name'       => 'modelOther',
                'attributes' => [
                    'type' => 'text',
                    'class' => 'form-control',
                    'id'    => 'other-model-v',
                    'maxlength' => self::LIMIT_MODEL_MAX,
                ],
                'options'    => [
                    'label' => 'Model, if other'
                ],
            ]
        );
        $this->add(
            [
                'name' => 'fuelType',
                'type' => Select::class,
                'options' => [
                    'label' => 'Fuel type',
                    'value_options' => $this->getDropdownOptions($options['vehicleData']['fuelType'])
                ]
            ]
        );
        $this->add(
            [
                'name' => 'cylinderCapacity',
                'attributes' => [
                    'type' => 'text',
                    'maxLength' => '5'
                ],
                'options' => [
                    'label' => 'Cylinder capacity',
                    'hint' => 'Enter a value in cc, for example, 1400'
                ]
            ]
        );
        $this->add(
            [
                'name' => 'vehicleClass',
                'type' => Select::class,
                'options' => [
                    'label' => 'Testing class',
                    'value_options' => $this->getDropdownOptions($options['vehicleData']['vehicleClass'])
                ]
            ]
        );
        $this->add(
            [
                'name' => 'colour',
                'type' => Select::class,
                'options' => [
                    'label' => 'Primary colour',
                    'value_options' => $this->getDropdownOptions($options['vehicleData']['colour'])
                ]
            ]
        );
        $this->add(
            [
                'name' => 'secondaryColour',
                'type' => Select::class,
                'options' => [
                    'label' => 'Secondary colour',
                    'value_options' => $this->getDropdownOptions($options['vehicleData']['secondaryColour'], false)
                ]
            ]
        );

        foreach ($this->getElements() as $element) {
            $element->setAttributes(
                [
                    'class' => 'form-control',
                    'id'    => $element->getName()
                ]
            );
        }
    }

    public function getInputFilterSpecification()
    {
        $spec = [
            [
                'name' => 'model',
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => CreateVehicleErrors::MODEL_EMPTY
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name'     => 'modelOther',
                'required' => false,
                'validators' => [
                    [
                        'name' => StringLength::class,
                        'options' => [
                            'messages' => [
                                StringLength::TOO_LONG => sprintf(CreateVehicleErrors::MODEL_MAX, self::LIMIT_MODEL_MAX + 1),
                            ],
                            'max' => self::LIMIT_MODEL_MAX,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'fuelType',
                'required' => true,
                'validators' => [
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => CreateVehicleErrors::FUEL_TYPE_EMPTY
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'vehicleClass',
                'required' => true,
                'validators' => [
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => $this->authorisedClasses[AuthorisedClassesService::KEY_FOR_PERSON_APPROVED_CLASSES],
                            'messages' => [
                                InArray::NOT_IN_ARRAY => sprintf(
                                    CreateVehicleErrors::CLASS_PERSON,
                                    $this->get('vehicleClass')->getValue()
                                ),
                            ]
                        ]
                    ],
                    [
                        'name' => InArray::class,
                        'options' => [
                            'haystack' => $this->authorisedClasses[AuthorisedClassesService::KEY_FOR_VTS_APPROVED_CLASSES],
                            'messages' => [
                                InArray::NOT_IN_ARRAY => sprintf(
                                    CreateVehicleErrors::CLASS_VTS,
                                    $this->get('vehicleClass')->getValue()
                                ),
                            ]
                        ]
                    ],
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => CreateVehicleErrors::CLASS_EMPTY
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'colour',
                'required' => true,
                'validators' =>
                    [
                        [
                            'name' => NotEmpty::class,
                            'options' => [
                                'messages' => [
                                    NotEmpty::IS_EMPTY => CreateVehicleErrors::COLOUR_EMPTY
                                ]
                            ]
                        ]
                    ]
            ],
            [
                'name' => 'secondaryColour',
                'required' => false,
            ],
        ];

        if (in_array($this->get('fuelType')->getValue(), FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacity())) {
            $spec[] = [
                'name' => 'cylinderCapacity',
                'required' => true,
                'validators' => [
                    [
                        'name' => Between::class,
                        'options' => [
                            'min' => self::LIMIT_CC_MIN,
                            'max' => self::LIMIT_CC_MAX,
                            'messages' => [
                                Between::NOT_BETWEEN => sprintf(CreateVehicleErrors::CC_NOT_BETWEEN, self::LIMIT_CC_MAX)
                            ]
                        ]
                    ],
                    [
                        'name' => IsInt::class,
                        'options' => [
                            'messages' =>
                                [
                                    IsInt::NOT_INT => CreateVehicleErrors::CC_INVALID
                                ]
                        ]
                    ],
                    [
                        'name' => NotEmpty::class,
                        'options' => [
                            'messages' => [
                                NotEmpty::IS_EMPTY => CreateVehicleErrors::CC_EMPTY
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $spec;
    }

    private function getDropdownOptions(&$source, $appendPleaseSelect = true)
    {
        return $this->getDropdownOptionsFromAttributes($source, 'id', 'name', $appendPleaseSelect);
    }

    private function getDropdownOptionsFromAttributes(&$source, $valueKey, $textKey, $appendPleaseSelect = true, $appendOther = false)
    {
        $options = [];
        if ($appendPleaseSelect) {
            $options [''] = self::LABEL_PLEASE_SELECT;
        }
        if (isset($source)) {
            foreach ($source as $s) {
                $options[$s[$valueKey]] = $s[$textKey];
            }
        }

        if ($appendOther) {
            $options[self::LABEL_OTHER_KEY] = self::LABEL_OTHER_VALUE;
        }

        return $options;
    }
}
