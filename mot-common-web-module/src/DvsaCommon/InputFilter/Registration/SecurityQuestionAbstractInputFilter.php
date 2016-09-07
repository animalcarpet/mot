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
 * (Account registration) Security question's steps (first and second) input filter.
 *
 * Class SecurityQuestionAbstractInputFilter
 */
abstract class SecurityQuestionAbstractInputFilter extends InputFilter
{
    /** To be used by firs and second security question's answers */
    const LIMIT_ANSWER_MAX = 70;

    /** Select a question to answer*/
    const MSG_QUESTION_EMPTY = 'choose a question';
    const MSG_QUESTION_NOT_NUMERIC = 'choose a valid question';

    /** Your answer*/
    const MSG_ANSWER_EMPTY = 'enter a memorable answer';
    const MSG_ANSWER_MAX = 'must be %d, or less, characters long';

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
                'name'       => static::FIELD_QUESTION,
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
                'name'       => static::FIELD_ANSWER,
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
                            'message' => sprintf(self::MSG_ANSWER_MAX, self::LIMIT_ANSWER_MAX),
                        ],
                    ],
                ],
            ]
        );
    }
}
