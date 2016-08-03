<?php

namespace DvsaMotTest\NewVehicle\Fieldset;

use DvsaMotTest\NewVehicle\Form\Validator\FirstRegistrationDateValidator;
use DvsaMotTest\NewVehicle\Form\Validator\NewVehicleEmptyVinReasonValidator;
use DvsaMotTest\NewVehicle\Form\Validator\NewVehicleEmptyVrmReasonValidator;
use DvsaMotTest\NewVehicle\Form\Validator\NewVehicleVinValidator;
use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use DvsaMotTest\NewVehicle\Form\Validator\NewVehicleVrmValidator;
use Zend\Form\Element\DateSelect;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Select;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\I18n\Validator\Alnum;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class CreateVehicleStepOneFieldset extends Fieldset implements InputFilterProviderInterface
{
    const LABEL_PLEASE_SELECT = 'Please select';
    const LABEL_OTHER_KEY = 'OTHER';
    const LABEL_OTHER_VALUE = 'Other';

    const CLASS_FORM_CONTROL = 'form-control';

    const LIMIT_MAKE_MAX = 30;

    private $emptyVrmVinReasons;

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $this->emptyVrmVinReasons = [
            'emptyVrmReasons' => $options['vehicleData']['emptyVrmReasons'] ?: [],
            'emptyVinReasons' => $options['vehicleData']['emptyVinReasons'] ?: [],
        ];

        $this->add(
            [
                'name' => 'countryOfRegistration',
                'type' => Select::class,
                'options' => [
                    'label' => 'Country of registration',
                    'value_options' => $this->getDropdownOptions(
                        $options['vehicleData']['countryOfRegistration']
                    ),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'registrationNumber',
                'attributes' => [
                    'type' => 'text',
                    'continue_if_empty' => true,
                ],
                'options' => [
                    'label' => 'Registration mark',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'emptyVrmReason',
                'type' => Select::class,
                'attributes' => [
                    'type' => 'text',
                    'class' => [
                        self::CLASS_FORM_CONTROL,
                    ],
                ],
                'options' => [
                    'label' => 'Reason for not supplying a registration mark',
                    'value_options' => $this->getDropdownOptionsFromAttributes(
                        $options['vehicleData']['emptyVrmReasons'],
                        'id',
                        'name',
                        true
                    ),
                ],
            ]
        );

        $this->add(
            [
                'name' => 'VIN',
                'attributes' => [
                    'type' => 'text',
                ],
                'options' => [
                    'label' => 'Full VIN or chassis number',
                ],
            ]
        );

        $this->add(
            [
                'name' => 'emptyVinReason',
                'type' => Select::class,
                'attributes' => [
                    'type' => 'text',
                    'class' => [
                        self::CLASS_FORM_CONTROL,
                    ],
                ],
                'options' => [
                    'label' => 'Reason for not supplying a full VIN or chassis number',
                    'value_options' => $this->getDropdownOptionsFromAttributes(
                        $options['vehicleData']['emptyVinReasons'],
                        'id',
                        'name',
                        true
                    ),
                ],
            ]
        );

        $makes = $this->getDropdownOptionsFromAttributes(
            $options['vehicleData']['make'],
            'id',
            'name',
            true,
            true
        );

        $this->add(
            [
                'name' => 'make',
                'type' => Select::class,
                'attributes' => [
                    'type' => 'text',
                    'class' => [
                        self::CLASS_FORM_CONTROL,
                        'form-group-related',
                    ],
                ],
                'options' => [
                    'label' => 'Manufacturer',
                    'value_options' => $makes
                ],
            ]
        );

        $this->add(
            [
                'name' => 'makeOther',
                'attributes' => [
                    'type' => 'text',
                    'id' => 'other-make-v',
                    'maxlength' => self::LIMIT_MAKE_MAX,
                ],
                'options' => [
                    'label' => 'Manufacturer, if other'
                ],
            ]
        );

        $this->add(
            [
                'name' => 'dateOfFirstUse',
                'type' => DateSelect::class,
                'options' => [
                    'label' => 'Approximate date of first use',
                    'format' => 'd-m-Y',
                    'hint' => 'For example, 21 04 1980',
                    'day_attributes' => [
                        'maxlength' => '2',
                    ],
                    'month_attributes' => [
                        'maxlength' => '2',
                    ],
                    'year_attributes' => [
                        'maxlength' => '4',
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name' => 'transmissionType',
                'type' => Radio::class,
                'options' => [
                    'label' => 'Transmission',
                    'value_options' => $this->getDropdownOptions(
                        $options['vehicleData']['transmissionType'],
                        false
                    ),
                    'label_attributes' => [
                        'class' => 'block-label'
                    ],
                ],
            ]
        );

        /** @var Element $element */
        foreach ($this->getElements() as $element) {

            if (!$element->hasAttribute('class')) {
                $element->setAttribute('class', self::CLASS_FORM_CONTROL);
            }

            if (!$element->hasAttribute('id')) {
                $element->setAttribute('id', $element->getName());
            }

        }
    }

    public function getInputFilterSpecification()
    {

        $spec = [];
        $spec[] = [
            'name' => 'dateOfFirstUse',
            'validators' => [
                [
                    'name' => FirstRegistrationDateValidator::class,
                    'options' => [
                        'messages' => [
                            FirstRegistrationDateValidator::IS_EMPTY => CreateVehicleErrors::DATE_EMPTY,
                            FirstRegistrationDateValidator::INVALID => CreateVehicleErrors::DATE_INVALID,
                            FirstRegistrationDateValidator::OLD_PAST => sprintf(
                                CreateVehicleErrors::DATE_MIN,
                                FirstRegistrationDateValidator::MIN_DATE
                            ),
                            FirstRegistrationDateValidator::FUTURE => CreateVehicleErrors::DATE_MAX,
                        ],
                    ],
                ],
            ],
        ];

        $spec[] = [
            'name' => 'registrationNumber',
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => NewVehicleVrmValidator::class,
                ],
            ]
        ];

        $spec[] = [
            'name' => 'emptyVrmReason',
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => NewVehicleEmptyVrmReasonValidator::class,
                    'options' => [
                        'reasons' => array_map(
                            function ($x) {
                                return $x['id'];
                            }, $this->getEmptyVrmReasons()
                        )
                    ]
                ],
            ]
        ];


        $spec[] = [
            'name' => 'VIN',
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => NewVehicleVinValidator::class,
                ],
            ]
        ];

        $spec[] = [
            'name' => 'emptyVinReason',
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => NewVehicleEmptyVinReasonValidator::class,
                    'options' => [
                        'reasons' => array_map(
                            function ($x) {
                                return $x['id'];
                            }, $this->getEmptyVinReasons()
                        )
                    ]
                ],
            ],
        ];
        $spec[] = [
            'name' => 'make',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => CreateVehicleErrors::MAKE_EMPTY,
                        ],
                    ],
                ],
            ],
        ];

        $spec[] = [
            'name' => 'makeOther',
            'required' => false,
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => self::LIMIT_MAKE_MAX,
                        'messages' => [
                            StringLength::TOO_LONG => sprintf(
                                CreateVehicleErrors::MAKE_OTHER_MAX,
                                self::LIMIT_MAKE_MAX + 1
                            ),
                        ],
                    ],
                ],
            ],
        ];

        $spec[] = [
            'name' => 'countryOfRegistration',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => CreateVehicleErrors::COUNTRY_EMPTY,
                        ],
                    ],
                ],
            ],
        ];

        $spec[] = [
            'name' => 'transmissionType',
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => CreateVehicleErrors::TRANSMISSION_EMPTY,
                        ],
                    ],
                ],
            ],
        ];

        return $spec;
    }

    public function getEmptyVrmReasons()
    {
        return $this->emptyVrmVinReasons['emptyVrmReasons'];
    }

    public function getEmptyVinReasons()
    {
        return $this->emptyVrmVinReasons['emptyVinReasons'];
    }

    private function getDropdownOptions(&$source, $appendPleaseSelect = true)
    {
        return $this->getDropdownOptionsFromAttributes($source, 'id', 'name', $appendPleaseSelect);
    }

    private function getDropdownOptionsFromAttributes(
        &$source,
        $valueKey,
        $textKey,
        $appendPleaseSelect = true,
        $appendOther = false
    ) {
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
