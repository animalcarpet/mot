<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommonTest\InputFilter\Account;

use DvsaCommon\InputFilter\Account\SetSecurityQuestionsAndAnswersInputFilter as SSQAIF;
use Zend\Validator\NotEmpty;

class SetSecurityQuestionsAndAnswersInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @param boolean $expectedToBeValid
     * @param array $expectedValidationMessages
     * @dataProvider validationRolesDataProvider
     */
    public function testValidationRoles($data, $expectedToBeValid, $expectedValidationMessages)
    {
        $inputFilter = new SSQAIF();
        $inputFilter->setData($data);

        $this->assertEquals(
            $expectedToBeValid,
            $inputFilter->isValid(),
            sprintf(
                'Failed to assert the following data set is %s: %s %s',
                $expectedToBeValid ? 'valid' : 'invalid',
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
        $standardIsEmptyMessage = [NotEmpty::IS_EMPTY => SSQAIF::MSG_IS_EMPTY,];

        $conditions = [
            [
                'data' => [
                    SSQAIF::FIELD_NAME_FIRST_QUESTION => 1,
                    SSQAIF::FIELD_NAME_SECOND_QUESTION => 2,
                ],
                'expectedToBeValid' => true,
                'expectedValidationMessages' => [],
            ],
            [
                'data' => [
                    SSQAIF::FIELD_NAME_FIRST_QUESTION => 2,
                ],
                'expectedToBeValid' => false,
                'expectedValidationMessages' => [SSQAIF::FIELD_NAME_SECOND_QUESTION => $standardIsEmptyMessage],
            ],
            [
                'data' => [
                    SSQAIF::FIELD_NAME_SECOND_QUESTION => 2,
                ],
                'expectedToBeValid' => false,
                'expectedValidationMessages' => [SSQAIF::FIELD_NAME_FIRST_QUESTION => $standardIsEmptyMessage],
            ],
        ];

        array_walk($conditions, function(&$condition) {
            $condition['data'][SSQAIF::FIELD_NAME_FIRST_ANSWER] = 'Acceptable answer for the first question';
            $condition['data'][SSQAIF::FIELD_NAME_SECOND_ANSWER] = 'Acceptable answer for the second question';
        });

        return $conditions;
    }
}
