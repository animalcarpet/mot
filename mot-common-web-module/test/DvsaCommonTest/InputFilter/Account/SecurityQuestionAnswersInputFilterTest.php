<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommonTest\InputFilter\Account;

use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter as SQIF;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class SecurityQuestionAnswersInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param boolean $expectedToBeValid
     * @param array $expectedValidationMessages
     * @dataProvider validationRolesDataProvider
     */
    public function testValidationRoles($data, $expectedToBeValid, $expectedValidationMessages)
    {
        $inputFilter = new SQIF();
        $inputFilter->setData($data);

        $this->assertEquals(
            $expectedToBeValid,
            $inputFilter->isValid(),
            sprintf(
                'Failed to assert the following data set is %s %s %s',
                $expectedToBeValid ? 'valid': 'invalid',
                PHP_EOL,
                print_r($data, true)
            )
        );

        $this->assertEquals($expectedValidationMessages, $inputFilter->getMessages());
    }

    /**
     * @return array
     */
    public function validationRolesDataProvider()
    {
        return array_merge(
            $this->getAcceptableData(),
            $this->getUnacceptableDataForFirstAnswer(),
            $this->getUnacceptableDataForSecondAnswer(),
            $this->getUnacceptableDataForBothAnswers()
        );
    }

    /**
     * @return array
     */
    private function getAcceptableData()
    {
        return [
            [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => 'Acceptable answer for the first question',
                    SQIF::FIELD_NAME_SECOND_ANSWER => 'Acceptable answer for the second question'
                ],
                'expectedToBeValid' => true,
                'expectedValidationMessages' => []
            ],
            [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => 'F',
                    SQIF::FIELD_NAME_SECOND_ANSWER => 'S'
                ],
                'expectedToBeValid' => true,
                'expectedValidationMessages' => []
            ],
            [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => '0',
                    SQIF::FIELD_NAME_SECOND_ANSWER => '0.0'
                ],
                'expectedToBeValid' => true,
                'expectedValidationMessages' => []
            ],
        ];
    }

    /**
     * @return array
     */
    private function getUnacceptableDataForFirstAnswer()
    {
        $data = [];

        foreach ($this->getUnacceptableConditions() as $condition) {
            $data[] = [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => $condition['value'],
                    SQIF::FIELD_NAME_SECOND_ANSWER => 'Acceptable answer for the second question',
                ],
                'expectedToBeValid' => false,
                'expectedValidationMessages' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => $condition['message'],
                ],
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getUnacceptableDataForSecondAnswer()
    {
        $data = [];

        foreach ($this->getUnacceptableConditions() as $condition) {
            $data[] = [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => 'Acceptable answer for the second question',
                    SQIF::FIELD_NAME_SECOND_ANSWER => $condition['value'],
                ],
                'expectedToBeValid' => false,
                'expectedValidationMessages' => [
                    SQIF::FIELD_NAME_SECOND_ANSWER => $condition['message'],
                ],
            ];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getUnacceptableDataForBothAnswers()
    {
        $data = [];

        foreach ($this->getUnacceptableConditions() as $condition) {
            $data[] = [
                'data' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => $condition['value'],
                    SQIF::FIELD_NAME_SECOND_ANSWER => $condition['value'],
                ],
                'expectedToBeValid' => false,
                'expectedValidationMessages' => [
                    SQIF::FIELD_NAME_FIRST_ANSWER => $condition['message'],
                    SQIF::FIELD_NAME_SECOND_ANSWER => $condition['message'],
                ],
            ];
        }

        return $data;
    }

    private function getUnacceptableConditions()
    {
        $msgNotEmpty = [NotEmpty::IS_EMPTY => SQIF::MSG_IS_EMPTY];
        $msgInvalid = [StringLength::INVALID => SQIF::MSG_INVALID_TYPE];

        return [
            [
                'value' => null,
                'message' => $msgNotEmpty,
            ],
            [
                'value' => false,
                'message' => $msgNotEmpty,
            ],
            [
                'value' => '',
                'message' => $msgNotEmpty,
            ],
            [
                'value' => ' ',
                'message' => $msgNotEmpty,
            ],
            [
                'value' => 0,
                'message' => $msgInvalid,
            ],
            [
                'value' => true,
                'message' => $msgInvalid,
            ],
            [
                'value' => str_repeat('a', SQIF::MAX_LENGTH + 1),
                'message' => [StringLength::TOO_LONG => sprintf(SQIF::MSG_TOO_LONG, SQIF::MAX_LENGTH + 1)],
            ],
        ];
    }
}
