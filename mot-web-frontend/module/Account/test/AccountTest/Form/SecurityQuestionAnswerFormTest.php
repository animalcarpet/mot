<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace AccountTest\Form;

use Account\Form\SecurityQuestionAnswersForm;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use Zend\Form\FormInterface;

class SecurityQuestionAnswerFormTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMappedQuestionAndAnswers()
    {
        $form = new SecurityQuestionAnswersForm(new SecurityQuestionDto(), new SecurityQuestionDto());

        $form->setData([
            SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_QUESTION_ID => 123,
            SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER => 'ABC',
            SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_QUESTION_ID=> 456,
            SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER => 'DEF',

        ]);

        $form->isValid();

        $this->assertEquals([
            123 => 'ABC',
            456 => 'DEF'
        ],
            $form->getMappedQuestionsAndAnswers());
    }
}
