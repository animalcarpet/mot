<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommonTest\InputFilter\Registration;

use DvsaCommon\Factory\InputFilter\Registration\ContactDetailsInputFilterFactory;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommonTest\Bootstrap;
use Zend\I18n\Validator\PostCode;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

class ContactDetailsInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContactDetailsInputFilter */
    private $subject;

    public function setUp()
    {
        $factory = new ContactDetailsInputFilterFactory();
        $this->subject = $factory->createService(Bootstrap::getServiceManager());
    }

    public function testInputFilterFactory()
    {
        $this->assertInstanceOf(
            ContactDetailsInputFilter::class,
            $this->subject
        );
    }

    /**
     * @param string[] $data Represent input fields name and value
     * @param boolean $isValid Expected state
     * @param array $messages Nested array of field names and related messages
     * @dataProvider dpDataAndExpectedResults
     */
    public function testValidators($data, $isValid, $errorMessages)
    {
        $this->subject->setData($data);

        $this->assertSame($isValid, $this->subject->isValid());

        $this->assertEquals($errorMessages, $this->subject->getMessages());
    }

    public function dpDataAndExpectedResults()
    {
        return [
            [
                // Test Invalid Postcode Character
                'data' => $this->prepareData(
                    'Valid Address 1',
                    'Valid Address 2',
                    'Valid Address 3',
                    'Valid City',
                    'B$8 1ER', // Invalid postcode format
                    '2918379371'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    [],
                    [
                        PostCode::NO_MATCH => ContactDetailsInputFilter::MSG_POSTCODE_EMPTY,
                    ],
                    []
                ),
            ],
            [
                // Test Invalid Postcode Format
                'data' => $this->prepareData(
                    'Valid Address 1',
                    'Valid Address 2',
                    'Valid Address 3',
                    'Valid City',
                    'BSS8 1ER', // Invalid postcode format
                    '2918379371'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    [],
                    [
                        PostCode::NO_MATCH => ContactDetailsInputFilter::MSG_POSTCODE_EMPTY,
                    ]
                ),
            ],
            [
                // Test All Blank Fields
                'data' => $this->prepareData('', '', '', '', '', ''),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [
                        NotEmpty::IS_EMPTY => ContactDetailsInputFilter::MSG_ADDRESS_EMPTY,
                        Regex::NOT_MATCH => ContactDetailsInputFilter::MSG_ADDRESS_LINE_CONTAINS_NO_ALPHANUMERIC,
                    ],
                    [],
                    [],
                    [
                        NotEmpty::IS_EMPTY => ContactDetailsInputFilter::MSG_TOWN_OR_CITY_EMPTY,
                        Regex::NOT_MATCH => ContactDetailsInputFilter::MSG_TOWN_NO_PATTERN_MATCH,
                    ],
                    [
                        NotEmpty::IS_EMPTY => ContactDetailsInputFilter::MSG_POSTCODE_EMPTY,
                        PostCode::NO_MATCH => ContactDetailsInputFilter::MSG_POSTCODE_EMPTY,
                    ],
                    [
                        NotEmpty::IS_EMPTY => ContactDetailsInputFilter::MSG_PHONE_INVALID,
                    ]
                ),
            ],
        ];
    }

    /**
     * @param $address1
     * @param $address2
     * @param $address3
     * @param $townOrCity
     * @param $postcode
     * @param $phone
     * @return array
     */
    public function prepareData(
        $address1,
        $address2,
        $address3,
        $townOrCity,
        $postcode,
        $phone
    ) {
        return [
            ContactDetailsInputFilter::FIELD_ADDRESS_1 => $address1,
            ContactDetailsInputFilter::FIELD_ADDRESS_2 => $address2,
            ContactDetailsInputFilter::FIELD_ADDRESS_3 => $address3,
            ContactDetailsInputFilter::FIELD_TOWN_OR_CITY => $townOrCity,
            ContactDetailsInputFilter::FIELD_POSTCODE => $postcode,
            ContactDetailsInputFilter::FIELD_PHONE => $phone,
        ];
    }

    /**
     * @param array $address1Messages
     * @param array $address2Messages
     * @param array $address3Messages
     * @param array $townOrCityMessages
     * @param array $postcodeMessages
     * @param array $phoneMessages
     * @return array
     */
    public function prepareMessages(
        $address1Messages = [],
        $address2Messages = [],
        $address3Messages = [],
        $townOrCityMessages = [],
        $postcodeMessages = [],
        $phoneMessages = []
    ) {
        $messages = [];

        if (!empty($address1Messages)) {
            $messages[ContactDetailsInputFilter::FIELD_ADDRESS_1] = $address1Messages;
        }
        if (!empty($address2Messages)) {
            $messages[ContactDetailsInputFilter::FIELD_ADDRESS_2] = $address2Messages;
        }
        if (!empty($address3Messages)) {
            $messages[ContactDetailsInputFilter::FIELD_ADDRESS_3] = $address3Messages;
        }
        if (!empty($townOrCityMessages)) {
            $messages[ContactDetailsInputFilter::FIELD_TOWN_OR_CITY] = $townOrCityMessages;
        }
        if (!empty($postcodeMessages)) {
            $messages[ContactDetailsInputFilter::FIELD_POSTCODE] = $postcodeMessages;
        }
        if (!empty($phoneMessages)) {
            $messages[ContactDetailsInputFilter::FIELD_PHONE] = $phoneMessages;
        }

        return $messages;
    }
}
