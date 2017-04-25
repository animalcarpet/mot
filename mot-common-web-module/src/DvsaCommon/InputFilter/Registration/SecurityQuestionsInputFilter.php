<?php
/**
 * This file is part of the DVSA MOT Common Web project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaCommon\InputFilter\Registration;

/**
 * (Account registration) Security questions step input filter.
 *
 * Class SecurityQuestionsInputFilter
 */
class SecurityQuestionsInputFilter extends SecurityQuestionAbstractInputFilter
{
    const FIELD_QUESTION_1 = 'question1';
    const FIELD_QUESTION_2 = 'question2';
    const FIELD_ANSWER_1 = 'answer1';
    const FIELD_ANSWER_2 = 'answer2';
}
