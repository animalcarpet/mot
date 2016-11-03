<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

use DvsaCommon\Validator\TelephoneNumberValidator;
use Zend\I18n\Validator\PostCode;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Hostname;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

/**
 * (Account registration) Your contact details step input filter.
 *
 * Class ContactDetailsInputFilter
 */
class ContactDetailsInputFilter extends InputFilter
{
    /** To be used by address line 1, 2, 3 since they have identical criteria */
    const LIMIT_ADDRESS_MAX = 50;
    const MSG_ADDRESS_LINE_NO_PATTERN_MATCH = 'must only contain letters, numbers and the symbols \',.-()&/';
    const MSG_ADDRESS_LINE_CONTAINS_NO_ALPHANUMERIC = 'must contain at least 1 letter or number';
    const ADDRESS_COMPONENT_MATCH_PATTERN = '/^[\\pL 0-9\'-,.\\(\\)\\/&]*$/u';
    const ADDRESS_COMPONENT_MATCH_ALPHA_PATTERN = '/[\\pL0-9]+/u';

    /** Address line 1 */
    const FIELD_ADDRESS_1 = 'address1';
    const MSG_ADDRESS_EMPTY = 'enter address line 1';
    const MSG_ADDRESS_MAX = 'must be %d, or less, characters long';

    /** Address line 2, optional with the same maximum limit as line one */
    const FIELD_ADDRESS_2 = 'address2';

    /** Address line 3, optional with the same maximum limit as line one */
    const FIELD_ADDRESS_3 = 'address3';

    /** Town or city */
    const FIELD_TOWN_OR_CITY = 'townOrCity';
    const MSG_TOWN_OR_CITY_EMPTY = 'enter a town or city';
    const MSG_TOWN_NO_PATTERN_MATCH = "must begin with a letter and only contain letters, spaces and the symbols -.,'";
    const TOWN_MATCH_PATTERN = "/^\\pL[\\pL -.,']*$/u";

    /** Postcode */
    const FIELD_POSTCODE = 'postcode';
    const MSG_POSTCODE_EMPTY = 'enter a valid postcode';
    const MSG_POSTCODE_MAX = 'must be %d, or less, characters long';

    /** Phone */
    const FIELD_PHONE = 'phone';
    const MSG_PHONE_MAX = 'must be %d characters or less';
    const MSG_PHONE_INVALID = 'enter a telephone number';

    public function init()
    {
        $this->initValidatorsForAddress(ContactDetailsInputFilter::FIELD_ADDRESS_1, true);
        $this->initValidatorsForAddress(ContactDetailsInputFilter::FIELD_ADDRESS_2);
        $this->initValidatorsForAddress(ContactDetailsInputFilter::FIELD_ADDRESS_3);
        $this->initValidatorsForTownAndCity();
        $this->initValidatorsForPostcode();
        $this->initValidatorPhone();
    }

    /**
     * Adding validators for the address line 1, 2 and 3 field/input.
     *
     * @param string     $fieldName  @see self::FIELD_ADDRESS_*
     * @param bool|false $isRequired (optional)
     */
    private function initValidatorsForAddress($fieldName, $isRequired = false)
    {
        $input = [

            'name'       => $fieldName,
            'required'   => $isRequired,
            'validators' => [
                [
                    'name'    => StringLength::class,
                    'options' => [
                        'max'     => self::LIMIT_ADDRESS_MAX,
                        'message' => sprintf(self::MSG_ADDRESS_MAX, self::LIMIT_ADDRESS_MAX),
                    ],
                ],
                [
                    'name' => Regex::class,
                    'options' => [
                        'pattern' => self::ADDRESS_COMPONENT_MATCH_PATTERN,
                        'message' => self::MSG_ADDRESS_LINE_NO_PATTERN_MATCH,
                    ],
                ],
                [
                    'name' => Regex::class,
                    'options' => [
                        'pattern' => self::ADDRESS_COMPONENT_MATCH_ALPHA_PATTERN,
                        'message' => self::MSG_ADDRESS_LINE_CONTAINS_NO_ALPHANUMERIC,
                    ],
                ],
            ],
        ];

        if ($isRequired) {
            array_unshift($input['validators'], [
                'name'    => NotEmpty::class,
                'options' => [
                    'message' => self::MSG_ADDRESS_EMPTY,
                ],
            ]);
        }

        $this->add($input);
    }

    /**
     * Adding validators for the Town and city field/input.
     */
    private function initValidatorsForTownAndCity()
    {
        $this->add(
            [
                'name'       => self::FIELD_TOWN_OR_CITY,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_TOWN_OR_CITY_EMPTY,
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max'     => self::LIMIT_ADDRESS_MAX,
                            'message' => sprintf(self::MSG_ADDRESS_MAX, self::LIMIT_ADDRESS_MAX),
                        ],
                    ],
                    [
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => self::TOWN_MATCH_PATTERN,
                            'message' => self::MSG_TOWN_NO_PATTERN_MATCH,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Adding validators for the postcode field/input.
     */
    private function initValidatorsForPostcode()
    {
        $this->add(
            [
                'name'       => self::FIELD_POSTCODE,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_POSTCODE_EMPTY,
                        ],
                    ],
                    [
                        'name' => PostCode::class,
                        'options' => [
                            'locale' => 'en_GB',
                            'message' => self::MSG_POSTCODE_EMPTY,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Adding validators for the phone field/input.
     */
    private function initValidatorPhone()
    {
        $this->add(
            [
                'name'       => self::FIELD_PHONE,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => TelephoneNumberValidator::class,
                        'options' => [
                            'allow'   => Hostname::ALLOW_ALL,
                            'message' => self::MSG_PHONE_INVALID,
                        ],
                    ],
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_PHONE_INVALID,
                        ],
                    ],
                ],
            ]
        );
    }
}
