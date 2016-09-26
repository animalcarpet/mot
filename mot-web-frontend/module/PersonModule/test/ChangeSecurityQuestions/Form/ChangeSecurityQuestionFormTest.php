<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Form;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionForm;

class ChangeSecurityQuestionFormTest extends \PHPUnit_Framework_TestCase
{
    const SECURITY_QUESTION_ONE_TEXT = 'questionOne';

    public function testIsValidIsTrueWhenQuestionChoiceAndAnswerValid()
    {
        $questions = $this->getSecurityQuestionData();
        $form = new ChangeSecurityQuestionForm($questions);

        $data = [
            ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER => self::SECURITY_QUESTION_ONE_TEXT,
            ChangeSecurityQuestionForm::FIELD_QUESTIONS => 'test answer'
        ];
        $form->setData($data);
        $this->assertTrue($form->isValid());
    }

    public function testIsValidIsFalseWhenQuestionNotChosenMessagesPresent()
    {
        $questions = $this->getSecurityQuestionData();
        $form = new ChangeSecurityQuestionForm($questions);

        $data = [
            ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER => 'this is an answer',
            ChangeSecurityQuestionForm::FIELD_QUESTIONS => '',
        ];

        $form->setData($data);
        $this->assertFalse($form->isValid());
        $actual = $form->getMessages();

        $this->assertCount(1, $actual);
        $this->assertSame(
            ChangeSecurityQuestionForm::MSG_INVALID_QUESTION_CHOICE,
            $actual[ChangeSecurityQuestionForm::FIELD_QUESTIONS][0]
        );
    }

    public function testIsValidIsFalseWhenQuestionAnswerNotChosen()
    {
        $questions = $this->getSecurityQuestionData();
        $form = new ChangeSecurityQuestionForm($questions);

        $data = [
            ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER => '',
            ChangeSecurityQuestionForm::FIELD_QUESTIONS => self::SECURITY_QUESTION_ONE_TEXT,
        ];

        $form->setData($data);
        $this->assertFalse($form->isValid());
        $actual = $form->getMessages();

        $this->assertCount(1, $actual);
        $this->assertSame(
            ChangeSecurityQuestionForm::MSG_ANSWER_EMPTY,
            $actual[ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER][0]
        );
    }

    public function testIsValidIsFalseWhenQuestionAndAnswerNotChosen()
    {
        $questions = $this->getSecurityQuestionData();
        $form = new ChangeSecurityQuestionForm($questions);

        $data = [
            ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER => '',
            ChangeSecurityQuestionForm::FIELD_QUESTIONS => ''
        ];

        $form->setData($data);
        $this->assertFalse($form->isValid());
        $actual = $form->getMessages();

        $this->assertCount(2, $actual);
        $this->assertSame(
            ChangeSecurityQuestionForm::MSG_ANSWER_EMPTY,
            $actual[ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER][0]
        );
        $this->assertSame(
            ChangeSecurityQuestionForm::MSG_INVALID_QUESTION_CHOICE,
            $actual[ChangeSecurityQuestionForm::FIELD_QUESTIONS][0]
        );
    }

    public function testIsValidIsFalseWhenAnswerOverMaxLength()
    {
        $stringExceedingMaxLength = 'cptbuwjhxyihnnhkxmpwonyeawckvxeqwzgolvqpxdqahnvpujmfodiynkjheeuxvnxlesv';
        $questions = $this->getSecurityQuestionData();
        $form = new ChangeSecurityQuestionForm($questions);

        $data = [
            ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER => $stringExceedingMaxLength,
            ChangeSecurityQuestionForm::FIELD_QUESTIONS => self::SECURITY_QUESTION_ONE_TEXT
        ];

        $form->setData($data);
        $this->assertFalse($form->isValid());
        $actual = $form->getMessages();

        $this->assertCount(1, $actual);
        $this->assertSame(
            ChangeSecurityQuestionForm::MSG_EXCEEDS_MAX_LENGTH,
            $actual[ChangeSecurityQuestionForm::FIELD_QUESTION_ANSWER][0]
        );
    }

    private function getSecurityQuestionData()
    {

        return [
            '1' => self::SECURITY_QUESTION_ONE_TEXT,
        ];
    }
}