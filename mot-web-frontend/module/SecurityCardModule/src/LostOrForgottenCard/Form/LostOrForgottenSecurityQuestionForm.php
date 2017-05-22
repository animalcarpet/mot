<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form;

use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class LostOrForgottenSecurityQuestionForm extends Form
{
    // underscores in form name gets replaced with spaces on zendFromErrorMessagesNew.phtml template for validation summary box
    const FORM_FIELD_NAME_ANSWER_ONE = 'First_question';
    const FORM_FIELD_NAME_ANSWER_TWO = 'Second_question';

    const FORM_FIELD_ID_ANSWER_ONE = 'answer1';
    const FORM_FIELD_ID_ANSWER_TWO = 'answer2';

    const MSG_ANSWER_IS_EMPTY = 'Enter your memorable answer';
    const MSG_EXCEEDS_MAX_LENGTH = 'Answer must be shorter than 71 characters';
    const MAX_LENGTH = 70;

    public function __construct($question1, $question2)
    {
        parent::__construct();

        $this->initializeFields($question1, $question2);
    }

    public function isValid()
    {
        $parentValid = parent::isValid();

        $answerFieldOne = $this->getAnswerOneField();
        $isFirstValid = $this->validateInputField($answerFieldOne);

        $answerFieldTwo = $this->getAnswerTwoField();
        $isSecondValid = $this->validateInputField($answerFieldTwo);

        return $parentValid && $isFirstValid && $isSecondValid;
    }

    /**
     * @return ElementInterface
     */
    public function getAnswerOneField()
    {
        return $this->get(self::FORM_FIELD_NAME_ANSWER_ONE);
    }

    /**
     * @return ElementInterface
     */
    public function getAnswerTwoField()
    {
        return $this->get(self::FORM_FIELD_NAME_ANSWER_TWO);
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $label
     */
    protected function addAnswerTextField($id, $name, $label)
    {
        $this->add((new Text())
            ->setName($name)
            ->setLabel($label)
            ->setAttribute('id', $id)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('type', 'text')
            ->setAttribute('autoCompleteOff', true)
        );
    }

    protected function initializeFields($questionLabel1, $questionLabel2)
    {
        $this->addAnswerTextField(
            self::FORM_FIELD_ID_ANSWER_ONE,
            self::FORM_FIELD_NAME_ANSWER_ONE,
            $questionLabel1
        );

        $this->addAnswerTextField(
            self::FORM_FIELD_ID_ANSWER_TWO,
            self::FORM_FIELD_NAME_ANSWER_TWO,
            $questionLabel2
        );
    }

    /**
     * @param ElementInterface $answerField
     * @return bool
     */
    protected function validateInputField(ElementInterface $answerField)
    {
        $answerValue = $answerField->getValue();

        if (empty($answerValue)) {
            $this->setCustomError($answerField, self::MSG_ANSWER_IS_EMPTY);

            return false;
        }

        if (mb_strlen($answerValue) > self::MAX_LENGTH) {
            $this->setCustomError($answerField, self::MSG_EXCEEDS_MAX_LENGTH);

            return false;
        }

        return true;
    }
}
