<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommonTest\InputFilter\Registration;

use DvsaCommon\Factory\InputFilter\Registration\SecurityQuestionsInputFilterFactory;
use DvsaCommon\InputFilter\Registration\SecurityQuestionAbstractInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use DvsaCommonTest\Bootstrap;
use Zend\Validator\Digits;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class SecurityQuestionsInputFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var SecurityQuestionsInputFilterFactory $securityQuestionsInputFilter */
    private $securityQuestionsInputFilter;

    public function setUp()
    {
        $factory = new SecurityQuestionsInputFilterFactory();

        $this->securityQuestionsInputFilter = $factory->createService(Bootstrap::getServiceManager());
    }

    public function testInputFilterFactory()
    {
        $this->assertContainsOnlyInstancesOf(
            SecurityQuestionAbstractInputFilter::class,
            [
                $this->securityQuestionsInputFilter,
            ]
        );
    }

    /**
     * @dataProvider securityQuestionDataAndExpectedResults
     *
     * @param string[] $data          Represent input fields name and value
     * @param bool     $isValid       Expected state
     * @param array    $errorMessages Nested array of field names and related messages
     */
    public function testValidators($data, $isValid, $errorMessages)
    {
        $this->securityQuestionsInputFilter->setData($data);

        $this->assertSame(
            $isValid,
            $this->securityQuestionsInputFilter->isValid(),
            sprintf(
                'Failed asserting isValid method on SecurityQuestionsInputFilter returns %s.',
                var_export($isValid, true)
            )
        );

        $this->assertEquals(
            $errorMessages,
            $this->securityQuestionsInputFilter->getMessages(),
            'Failed asserting validation message on SecurityQuestion%sInputFilter.'
        );
    }

    public function securityQuestionDataAndExpectedResults()
    {
        $data = [
            [
                'data' => $this->prepareData(
                    1,
                    'answer',
                    2,
                    'answer'
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
                'data' => $this->prepareData(
                    '',
                    '',
                    '',
                    ''
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_EMPTY,
                        Digits::STRING_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_NOT_NUMERIC,
                    ],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_ANSWER_EMPTY,
                    ],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_EMPTY,
                        Digits::STRING_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_NOT_NUMERIC,
                    ],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_ANSWER_EMPTY,
                    ]
                ),
            ],
            [
                'data' => $this->prepareData(
                    1,
                    'answer',
                    '',
                    ''
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_EMPTY,
                        Digits::STRING_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_NOT_NUMERIC,
                    ],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_ANSWER_EMPTY,
                    ]
                ),
            ],
            [
                'data' => $this->prepareData(
                    1,
                    '',
                    6,
                    ''
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_ANSWER_EMPTY,
                    ],
                    [],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_ANSWER_EMPTY,
                    ]
                ),
            ],
            [
                'data' => $this->prepareData(
                    '',
                    'answer',
                    '',
                    'answer'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_EMPTY,
                        Digits::STRING_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_NOT_NUMERIC,
                    ],
                    [],
                    [
                        NotEmpty::IS_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_EMPTY,
                        Digits::STRING_EMPTY => SecurityQuestionAbstractInputFilter::MSG_QUESTION_NOT_NUMERIC,
                    ],
                    []
                ),
            ],
            [
                'data' => $this->prepareData(
                    1,
                    str_repeat('a', SecurityQuestionAbstractInputFilter::LIMIT_ANSWER_MAX + 1),
                    6,
                    'answer'
                ),
                'isValid' => false,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [
                        StringLength::TOO_LONG => sprintf(
                                SecurityQuestionAbstractInputFilter::MSG_ANSWER_MAX,
                                SecurityQuestionAbstractInputFilter::LIMIT_ANSWER_MAX
                            ),
                    ],
                    [],
                    []
                ),
            ],
            [
                'data' => $this->prepareData(
                    1,
                    str_repeat('a', SecurityQuestionAbstractInputFilter::LIMIT_ANSWER_MAX),
                    6,
                    'answer'
                ),
                'isValid' => true,
                'errorMessages' => $this->prepareMessages(
                    [],
                    [],
                    [],
                    []
                ),
            ],
        ];

        return $data;
    }

    /**
     * @param int    $question1
     * @param string $answer1
     * @param int    $question2
     * @param string $answer2
     *
     * @return array
     */
    public function prepareData(
        $question1,
        $answer1,
        $question2,
        $answer2
    ) {
        return [
            SecurityQuestionsInputFilter::FIELD_QUESTION_1 => $question1,
            SecurityQuestionsInputFilter::FIELD_ANSWER_1 => $answer1,
            SecurityQuestionsInputFilter::FIELD_QUESTION_2 => $question2,
            SecurityQuestionsInputFilter::FIELD_ANSWER_2 => $answer2,
        ];
    }

    /**
     * @param array $question1Messages
     * @param array $answer1Messages
     * @param array $question2Messages
     * @param array $answer2Messages
     *
     * @return array
     */
    public function prepareMessages(
        $question1Messages,
        $answer1Messages,
        $question2Messages,
        $answer2Messages
    ) {
        $messages = [];

        if (!empty($question1Messages)) {
            $messages[SecurityQuestionsInputFilter::FIELD_QUESTION_1] = $question1Messages;
        }

        if (!empty($answer1Messages)) {
            $messages[SecurityQuestionsInputFilter::FIELD_ANSWER_1] = $answer1Messages;
        }

        if (!empty($question2Messages)) {
            $messages[SecurityQuestionsInputFilter::FIELD_QUESTION_2] = $question2Messages;
        }

        if (!empty($answer2Messages)) {
            $messages[SecurityQuestionsInputFilter::FIELD_ANSWER_2] = $answer2Messages;
        }

        return $messages;
    }
}
