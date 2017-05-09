<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;

class RegisteredEmailPropertyForm extends Form
{
    const FIELD_EMAIL = UpdateAePropertyAction::AE_REGISTERED_EMAIL_PROPERTY;
    const FIELD_EMAIL_MAX_LENGTH = 255;

    const EMAIL_ADDRESS_INVALID_MSG = 'you must enter a valid email address';
    const EMAIL_ADDRESS_TOO_LONG_MSG = 'must be %max% characters or less';

    private $emailElement;

    public function __construct()
    {
        parent::__construct(static::FIELD_EMAIL);

        $this->emailElement = new Text();
        $this->emailElement->setName(static::FIELD_EMAIL)
            ->setLabel('Email address')
            ->setAttribute('id', 'email')
            ->setAttribute('required', false)
            ->setAttribute('group', true);

        $this->add($this->emailElement);

        $emailFilter = new Input($this->emailElement->getName());

        $emailInvalidValidator = (new EmailAddressValidator())->setMessage(static::EMAIL_ADDRESS_INVALID_MSG);
        $emailEmptyValidator = (new StringLength())
            ->setMax(static::FIELD_EMAIL_MAX_LENGTH)
            ->setMessage(static::EMAIL_ADDRESS_TOO_LONG_MSG, StringLength::TOO_LONG);

        $emailFilter
            ->setRequired(false)
            ->getValidatorChain()
            ->attach($emailInvalidValidator)
            ->attach($emailEmptyValidator);

        $filter = new InputFilter();
        $filter->add($emailFilter);

        $this->setInputFilter($filter);
    }

    public function getEmailElement()
    {
        return $this->emailElement;
    }
}
