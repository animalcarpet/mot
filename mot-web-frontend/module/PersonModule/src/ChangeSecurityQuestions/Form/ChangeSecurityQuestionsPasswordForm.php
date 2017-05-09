<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form;

use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class ChangeSecurityQuestionsPasswordForm extends Form
{
    const FIELD_PASSWORD = 'Password';
    const MSG_EMPTY_PASSWORD = 'Enter your password';
    const MSG_PROBLEM_WITH_PASSWORD = 'There was a problem with your password.  Please try again.';
    const PASSWORD_LABEL = 'Your password';

    public function __construct()
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::FIELD_PASSWORD)
            ->setLabel('Your password')
            ->setAttribute('id', self::FIELD_PASSWORD)
            ->setAttribute('required', true)
            ->setAttribute('type', 'password')
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
        );
    }

    public function isValid()
    {
        $passwordField = $this->getPassword();

        if (empty($passwordField->getValue())) {
            $this->setCustomError($passwordField, self::MSG_EMPTY_PASSWORD);
            $this->showLabelOnError(self::FIELD_PASSWORD, self::PASSWORD_LABEL);

            return false;
        }

        return parent::isValid();
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
     * @return ElementInterface
     */
    public function getPassword()
    {
        return $this->get(self::FIELD_PASSWORD);
    }

    public function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}
