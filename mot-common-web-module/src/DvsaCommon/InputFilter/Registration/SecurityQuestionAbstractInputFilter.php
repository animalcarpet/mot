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
use Zend\Validator\Digits;

/**
 * (Account registration) Security questions step input filter.
 *
 * Class SecurityQuestionAbstractInputFilter
 */
abstract class SecurityQuestionAbstractInputFilter extends InputFilter
{
    // To be used by first and second security question's answers
    const LIMIT_ANSWER_MAX = 70;

    // Select a question to answer
    const MSG_QUESTION_EMPTY = 'choose a question';
    const MSG_QUESTION_NOT_NUMERIC = 'choose a valid question';

    // Your answer
    const MSG_ANSWER_EMPTY = 'enter an answer';
    const MSG_ANSWER_MAX = 'must be shorter than 71 characters';

    public function init()
    {
        $this->initValidatorsForQuestion();
        $this->initValidatorsForAnswer();
    }

    /**
     * Adding validators for the question's field/input.
     */
    private function initValidatorsForQuestion()
    {
        $this->add(
            [
                'name'       => SecurityQuestionsInputFilter::FIELD_QUESTION_1,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_QUESTION_EMPTY,
                        ],
                    ],
                    [
                        'name' => Digits::class,
                        'options' => [
                            'message' => self::MSG_QUESTION_NOT_NUMERIC,
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => SecurityQuestionsInputFilter::FIELD_QUESTION_2,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_QUESTION_EMPTY,
                        ],
                    ],
                    [
                        'name' => Digits::class,
                        'options' => [
                            'message' => self::MSG_QUESTION_NOT_NUMERIC,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Adding validators for the answer's field/input.
     */
    private function initValidatorsForAnswer()
    {
        $this->add(
            [
                'name'       => SecurityQuestionsInputFilter::FIELD_ANSWER_1,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_ANSWER_EMPTY,
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max'     => self::LIMIT_ANSWER_MAX,
                            'message' => self::MSG_ANSWER_MAX,
                        ],
                    ],
                ],
            ]
        );

        $this->add(
            [
                'name'       => SecurityQuestionsInputFilter::FIELD_ANSWER_2,
                'required'   => true,
                'validators' => [
                    [
                        'name'    => NotEmpty::class,
                        'options' => [
                            'message' => self::MSG_ANSWER_EMPTY,
                        ],
                    ],
                    [
                        'name'    => StringLength::class,
                        'options' => [
                            'max'     => self::LIMIT_ANSWER_MAX,
                            'message' => self::MSG_ANSWER_MAX,
                        ],
                    ],
                ],
            ]
        );
    }
}
