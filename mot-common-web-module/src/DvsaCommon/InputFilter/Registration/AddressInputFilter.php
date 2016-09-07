<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

/**
 * (Account registration) Your address' step input filter.
 *
 * Class AddressInputFilter
 */
class AddressInputFilter extends InputFilter
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
    const POSTCODE_MATCH_PATTERN = '/^(([a-z]{2}([0-9]{1,2}|[0-9][a-z]))|([a-z]([0-9]{1,2}|[0-9][a-z])))\s?[0-9][abd-hjlnp-uw-z]{2}$/i';

    public function init()
    {
        $this->initValidatorsForAddress(AddressInputFilter::FIELD_ADDRESS_1, true);
        $this->initValidatorsForAddress(AddressInputFilter::FIELD_ADDRESS_2);
        $this->initValidatorsForAddress(AddressInputFilter::FIELD_ADDRESS_3);
        $this->initValidatorsForTownAndCity();
        $this->initValidatorsForPostcode();
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
                        'name' => Regex::class,
                        'options' => [
                            'pattern' => self::POSTCODE_MATCH_PATTERN,
                            'message' => self::MSG_POSTCODE_EMPTY,
                        ],
                    ],
                ],
            ]
        );
    }
}
