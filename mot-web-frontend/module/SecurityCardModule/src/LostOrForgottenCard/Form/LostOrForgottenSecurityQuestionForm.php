<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form;

use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class LostOrForgottenSecurityQuestionForm extends Form
{
    const ANSWER = 'answer';
    const MSG_ANSWER_IS_EMPTY = 'You must answer the question';
    const MSG_EXCEEDS_MAX_LENGTH = 'Memorable answer must be less than 70 characters long';
    const MAX_LENGTH = 70;

    public function __construct($answerLabel)
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::ANSWER)
            ->setLabel($answerLabel)
            ->setAttribute('id', self::ANSWER)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('type', 'text')
            ->setAttribute('autoCompleteOff', true)
        );
    }

    public function isValid()
    {
        $parentValid = parent::isValid();

        $answerField = $this->getAnswerField();
        $answerValue = $answerField->getValue();

        if (empty($answerValue)) {
            $this->setCustomError($answerField, self::MSG_ANSWER_IS_EMPTY);
            return false;
        }

        if (strlen($answerValue) > self::MAX_LENGTH) {
            $this->setCustomError($answerField, self::MSG_EXCEEDS_MAX_LENGTH);
            return false;
        }

        return $parentValid;
    }

    public function getAnswerField()
    {
        return $this->get(self::ANSWER);
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }
}