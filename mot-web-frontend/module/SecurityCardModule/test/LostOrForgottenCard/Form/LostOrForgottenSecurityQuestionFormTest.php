<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form\LostOrForgottenSecurityQuestionForm;

class LostOrForgottenSecurityQuestionFormTest extends \PHPUnit_Framework_TestCase
{
    const QUESTION_LABEL1 = 'What is your birthday?';
    const QUESTION_LABEL2 = 'Where did you go on your first memorable holiday?';
    const TEXT_EXCEEDING_MAX = 'HAcHg4VIlIWD9vTki8SRVLkXGhuZKv26UhOmE1fiYx4hkq2WDk1JuefI3kXfSKgQgURTIqU';

    public function testFormLabelIsPopulatedCorrectly()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL1, self::QUESTION_LABEL2);

        $this->assertEquals(self::QUESTION_LABEL1, $form->getAnswerOneField()->getLabel());
        $this->assertEquals(self::QUESTION_LABEL2, $form->getAnswerTwoField()->getLabel());
    }

    public function testFormWithEmptyInputHasErrorMessage()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL1, self::QUESTION_LABEL2);

        $data = [
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE => '',
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_TWO => ''
        ];

        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages(LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE ));
        $this->assertFalse($isvalid);
        $this->assertEquals(LostOrForgottenSecurityQuestionForm::MSG_ANSWER_IS_EMPTY, reset($messages));
        $this->assertCount(1, $messages);
    }

    public function testFormWithInputExceedingMaxInputHasErrorMessage()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL1, self::QUESTION_LABEL2);

        $data = [
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE => self::TEXT_EXCEEDING_MAX
        ];

        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages(LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE));
        $this->assertFalse($isvalid);
        $this->assertEquals(LostOrForgottenSecurityQuestionForm::MSG_EXCEEDS_MAX_LENGTH, reset($messages));
        $this->assertCount(1, $messages);
    }

    public function testFormWithValidInputHasNoErrorMessages()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL1, self::QUESTION_LABEL2);

        $data = [
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE => 'Valid answer',
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_TWO => 'Valid answer',
        ];
        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages(LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE));
        $this->assertTrue($isvalid);
        $this->assertCount(0, $messages);
    }

    public function testCustomErrorCodeAddsMessageToField()
    {
        $errorMessage = 'Something went wrong';
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL1, self::QUESTION_LABEL2);
        $answerField = $form->getAnswerOneField();

        $form->setCustomError($answerField, $errorMessage);

        $messages = array_values($form->getMessages(LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE));
        $this->assertEquals($errorMessage, reset($messages));
        $this->assertCount(1, $messages);
    }
}
