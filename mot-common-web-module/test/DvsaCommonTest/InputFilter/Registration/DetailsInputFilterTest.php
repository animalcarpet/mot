<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommonTest\InputFilter\Registration;

use DvsaCommon\Factory\InputFilter\Registration\DetailsInputFilterFactory;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\Validator\DateOfBirthValidator;
use DvsaCommonTest\Bootstrap;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

class DetailsInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var DetailsInputFilter */
    private $subject;

    public function setUp()
    {
        $factory = new DetailsInputFilterFactory();
        $this->subject = $factory->createService(Bootstrap::getServiceManager());
    }

    public function testInputFilterFactory()
    {
        $this->assertInstanceOf(
            DetailsInputFilter::class,
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
                // Test Valid Data
                'data' => $this->prepareData(
                    'Joe',
                    'Light',
                    'Brown',
                    '01',
                    '02',
                    '1990'
                ),
                'isValid' => true,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    []
                ),
            ],
            [
                // Test Empty Data Set
                'data' => $this->prepareData(
                    '',
                    '',
                    '',
                    '',
                    '',
                    ''
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [
                        NotEmpty::IS_EMPTY => DetailsInputFilter::MSG_FIRST_NAME_EMPTY,
                        Regex::NOT_MATCH => DetailsInputFilter::MSG_NAME_NO_PATTERN_MATCH,
                    ],
                    [],
                    [
                        NotEmpty::IS_EMPTY => DetailsInputFilter::MSG_LAST_NAME_EMPTY,
                        Regex::NOT_MATCH => DetailsInputFilter::MSG_NAME_NO_PATTERN_MATCH,
                    ],
                    [
                        DateOfBirthValidator::IS_EMPTY => DateOfBirthValidator::ERR_MSG_IS_EMPTY,
                    ]
                ),
            ],
            [
                // Test Invalid Name
                'data' => $this->prepareData(
                    'J0£',
                    'L1ght',
                    'Br0wn',
                    '01',
                    '02',
                    '1990'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [Regex::NOT_MATCH => DetailsInputFilter::MSG_NAME_NO_PATTERN_MATCH],
                    [Regex::NOT_MATCH => DetailsInputFilter::MSG_NAME_NO_PATTERN_MATCH],
                    [Regex::NOT_MATCH => DetailsInputFilter::MSG_NAME_NO_PATTERN_MATCH],
                    []
                ),
            ],
            [
                // Test Invalid Date format
                'data' => $this->prepareData(
                    'Joe',
                    'Light',
                    'Brown',
                    'ss',
                    'ss',
                    'ssss'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    [DateOfBirthValidator::IS_INVALID_FORMAT => DateOfBirthValidator::ERR_MSG_IS_INVALID_FORMAT]
                ),
            ],
            [
                // Test Date over 100 years old
                'data' => $this->prepareData(
                    'Joe',
                    'Light',
                    'Brown',
                    '01',
                    '02',
                    $this->getOldDate()
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    [DateOfBirthValidator::IS_OVER100 => DateOfBirthValidator::ERR_MSG_IS_OVER100]
                ),
            ],
            [
                // Test Date in the future
                'data' => $this->prepareData(
                    'Joe',
                    'Light',
                    'Brown',
                    '01',
                    '02',
                    $this->getFutureDate()
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    [DateOfBirthValidator::IS_FUTURE => DateOfBirthValidator::ERR_MSG_IS_FUTURE]
                ),
            ],
        ];
    }

    /**
     * @param $firstName
     * @param $middleName
     * @param $lastName
     * @param $day
     * @param $month
     * @param $year
     * @return array
     */
    public function prepareData(
        $firstName,
        $middleName,
        $lastName,
        $day,
        $month,
        $year
    ) {
        return [
            DetailsInputFilter::FIELD_FIRST_NAME => $firstName,
            DetailsInputFilter::FIELD_MIDDLE_NAME => $middleName,
            DetailsInputFilter::FIELD_LAST_NAME => $lastName,
            DetailsInputFilter::FIELD_DATE => [
                DetailsInputFilter::FIELD_DAY => $day,
                DetailsInputFilter::FIELD_MONTH => $month,
                DetailsInputFilter::FIELD_YEAR => $year,
            ],
        ];
    }

    /**
     * @param array $firstNameMessages
     * @param array $middleNameMessages
     * @param array $lastNameMessages
     * @param array $dateMessages
     * @return array
     */
    public function prepareMessages(
        $firstNameMessages = [],
        $middleNameMessages = [],
        $lastNameMessages = [],
        $dateMessages = []
    ) {
        $messages = [];

        if (!empty($firstNameMessages)) {
            $messages[DetailsInputFilter::FIELD_FIRST_NAME] = $firstNameMessages;
        }
        if (!empty($middleNameMessages)) {
            $messages[DetailsInputFilter::FIELD_MIDDLE_NAME] = $middleNameMessages;
        }
        if (!empty($lastNameMessages)) {
            $messages[DetailsInputFilter::FIELD_LAST_NAME] = $lastNameMessages;
        }
        if (!empty($dateMessages)) {
            $messages[DetailsInputFilter::FIELD_DATE] = $dateMessages;
        }
        return $messages;
    }

    private function getOldDate()
    {
        $date = new \DateTime('-101 years');
        return $date->format('Y');
    }

    private function getFutureDate()
    {
        $date = new \DateTime('+1 year');
        return $date->format('Y');
    }
}
