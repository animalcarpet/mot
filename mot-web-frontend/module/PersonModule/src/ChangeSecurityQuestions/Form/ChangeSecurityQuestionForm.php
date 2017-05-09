<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form;

use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class ChangeSecurityQuestionForm extends Form
{
    const FIELD_QUESTIONS = 'questions';
    const FIELD_QUESTION_ANSWER = 'question-answer';
    const MSG_INVALID_QUESTION_CHOICE = 'You must choose a security question';
    const MSG_ANSWER_EMPTY = 'Enter a memorable answer';
    const MSG_EXCEEDS_MAX_LENGTH = 'Memorable answer must be less than 70 characters long';
    const MAX_LENGTH = 70;

    public function __construct(array $securityQuestions)
    {
        parent::__construct();

        $this->add((new Select())
            ->setName(self::FIELD_QUESTIONS)
            ->setLabel(self::FIELD_QUESTIONS)
            ->setAttribute('id', self::FIELD_QUESTIONS)
            ->setAttribute('required', true)
            ->setValueOptions($securityQuestions)
            ->setOption('label_attributes', ['class' => 'block-label'])
        );

        $this->add((new Text())
            ->setName(self::FIELD_QUESTION_ANSWER)
            ->setLabel('Enter your answer')
            ->setAttribute('id', self::FIELD_QUESTION_ANSWER)
            ->setAttribute('required', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('group', true)
        );
    }

    public function isValid()
    {
        $fieldsValid = true;
        $securityQuestions = $this->getSecurityQuestion();
        $securityQuestionAnswer = $this->getSecurityQuestionAnswer();
        $answerValue = trim($securityQuestionAnswer->getValue());

        if ($securityQuestions->getValue() == '') {
            $this->setCustomError($securityQuestions, self::MSG_INVALID_QUESTION_CHOICE);
            $this->showLabelOnError(self::FIELD_QUESTIONS, 'Choose a question');
            $fieldsValid = false;
        }

        if (empty($answerValue)) {
            $this->setCustomError($securityQuestionAnswer, self::MSG_ANSWER_EMPTY);
            $this->showLabelOnError(self::FIELD_QUESTION_ANSWER, 'Your answer');
            $fieldsValid = false;
        } elseif (strlen($answerValue) > self::MAX_LENGTH) {
            $this->setCustomError($securityQuestionAnswer, self::MSG_EXCEEDS_MAX_LENGTH);
            $this->showLabelOnError(self::FIELD_QUESTION_ANSWER, 'Your answer');
            $fieldsValid = false;
        }

        return $fieldsValid;
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    public function getSecurityQuestion()
    {
        return $this->get(self::FIELD_QUESTIONS);
    }

    public function getSecurityQuestionAnswer()
    {
        return $this->get(self::FIELD_QUESTION_ANSWER);
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}
