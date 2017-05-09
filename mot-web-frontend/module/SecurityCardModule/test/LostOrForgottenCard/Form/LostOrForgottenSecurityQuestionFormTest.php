<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form\LostOrForgottenSecurityQuestionForm;

class LostOrForgottenSecurityQuestionFormTest extends \PHPUnit_Framework_TestCase
{
    const QUESTION_LABEL = 'What is your birthday?';
    const TEXT_EXCEEDING_MAX = 'HAcHg4VIlIWD9vTki8SRVLkXGhuZKv26UhOmE1fiYx4hkq2WDk1JuefI3kXfSKgQgURTIqU';

    public function testFormLabelIsPopulatedCorrectly()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL);

        $this->assertEquals(self::QUESTION_LABEL, $form->getAnswerField()->getLabel());
    }

    public function testFormWithEmptyInputHasErrorMessage()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL);

        $data = ['answer' => ''];
        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages('answer'));
        $this->assertFalse($isvalid);
        $this->assertEquals(LostOrForgottenSecurityQuestionForm::MSG_ANSWER_IS_EMPTY, reset($messages));
        $this->assertCount(1, $messages);
    }

    public function testFormWithInputExceedingMaxInputHasErrorMessage()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL);

        $data = ['answer' => self::TEXT_EXCEEDING_MAX];
        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages('answer'));
        $this->assertFalse($isvalid);
        $this->assertEquals(LostOrForgottenSecurityQuestionForm::MSG_EXCEEDS_MAX_LENGTH, reset($messages));
        $this->assertCount(1, $messages);
    }

    public function testFormWithValidInputHasNoErrorMessages()
    {
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL);

        $data = ['answer' => 'Valid answer'];
        $form->setData($data);
        $isvalid = $form->isValid();

        $messages = array_values($form->getMessages('answer'));
        $this->assertTrue($isvalid);
        $this->assertCount(0, $messages);
    }

    public function testCustomErrorCodeAddsMessageToField()
    {
        $errorMessage = 'Something went wrong';
        $form = new LostOrForgottenSecurityQuestionForm(self::QUESTION_LABEL);
        $answerField = $form->getAnswerField();

        $form->setCustomError($answerField, $errorMessage);

        $messages = array_values($form->getMessages('answer'));
        $this->assertEquals($errorMessage, reset($messages));
        $this->assertCount(1, $messages);
    }
}
