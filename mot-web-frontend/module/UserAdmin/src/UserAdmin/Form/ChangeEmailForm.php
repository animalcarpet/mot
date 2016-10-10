<?php

namespace UserAdmin\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class ChangeEmailForm extends Form
{
    const FIELD_EMAIL = 'email';
    const FIELD_EMAIL_CONFIRM = 'emailConfirm';

    const FIELD_EMAIL_LABEL = 'Email address';
    const FIELD_EMAIL_CONFIRM_LABEL = 'Re-type your email address';

    const MAX_EMAIL_LENGTH = 255;

    const MSG_EMAIL_CHANGED_SUCCESS = 'Email address has been changed successfully.';
    const MSG_EMAIL_CHANGED_FAILURE = 'Email address could not be changed. Please try again.';

    const MSG_DUPLICATE_EMAIL_ERROR = 'This email address is already in use. Each account must have a different email address.';
    const MSG_BLANK_EMAIL_ERROR = 'Enter your email address';
    const MSG_INVALID_EMAIL_ERROR = 'Enter a valid email address';
    const MSG_EMAILS_DONT_MATCH_ERROR = 'The email addresses must be the same';
    const MSG_MAX_LENGTH_ERROR = 'Must be 255 characters or less';

    public function __construct($email = null)
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::FIELD_EMAIL)
            ->setLabel('Email address')
            ->setAttribute('id', self::FIELD_EMAIL)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('value', $email)
            ->setAttribute('divModifier', 'form-group')
        );

        $this->add((new Text())
            ->setName(self::FIELD_EMAIL_CONFIRM)
            ->setLabel('Re-type your email address')
            ->setAttribute('id', self::FIELD_EMAIL_CONFIRM)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('value', $email)
            ->setAttribute('divModifier', 'form-group')
        );
    }

    public function getEmailConfirm()
    {
        return $this->get(self::FIELD_EMAIL_CONFIRM);
    }

    public function getEmail()
    {
        return $this->get(self::FIELD_EMAIL);
    }

    public function isValid()
    {
        $fieldsValid = true;
        $email = $this->getEmail()->getValue();
        $emailConfirm = $this->getEmailConfirm()->getValue();
        $validator = new EmailAddressValidator();

        if ($email != $emailConfirm) {
            $this->setCustomError(self::getEmailConfirm(), self::MSG_EMAILS_DONT_MATCH_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL_CONFIRM, self::FIELD_EMAIL_CONFIRM_LABEL);
            $fieldsValid = false;
        }

        if (strlen($email) > self::MAX_EMAIL_LENGTH) {
            $this->setCustomError(self::getEmail(), self::MSG_MAX_LENGTH_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL, self::FIELD_EMAIL_LABEL);
            $fieldsValid = false;
        }

        if (strlen($emailConfirm) > self::MAX_EMAIL_LENGTH) {
            $this->setCustomError(self::getEmailConfirm(), self::MSG_MAX_LENGTH_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL_CONFIRM, self::FIELD_EMAIL_CONFIRM_LABEL);
            $fieldsValid = false;
        }

        if (!empty($email) && !$validator->isValid($email) && strlen($email) < self::MAX_EMAIL_LENGTH) {
            $this->setCustomError(self::getEmail(), self::MSG_INVALID_EMAIL_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL, self::FIELD_EMAIL_LABEL);
            $fieldsValid = false;
        }

        if (!empty($emailConfirm) && !$validator->isValid($emailConfirm) && strlen($emailConfirm) < self::MAX_EMAIL_LENGTH) {
            $this->setCustomError(self::getEmailConfirm(), self::MSG_INVALID_EMAIL_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL_CONFIRM, self::FIELD_EMAIL_CONFIRM_LABEL);
            $fieldsValid = false;
        }

        if (empty($email) && !$validator->isValid($email)) {
            $this->setCustomError(self::getEmail(), self::MSG_BLANK_EMAIL_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL, self::FIELD_EMAIL_LABEL);
            $fieldsValid = false;
        }

        if (empty($emailConfirm) && !$validator->isValid($emailConfirm)) {
            $this->setCustomError(self::getEmailConfirm(), self::MSG_BLANK_EMAIL_ERROR);
            $this->showLabelOnError(self::FIELD_EMAIL_CONFIRM, self::FIELD_EMAIL_CONFIRM_LABEL);
            $fieldsValid = false;
        }

        return $fieldsValid;
    }

    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }
}