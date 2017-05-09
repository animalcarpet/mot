<?php

namespace Site\UpdateVtsProperty\Process\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\StringLength;

class EmailPropertyForm extends Form
{
    const FIELD_EMAIL = UpdateVtsPropertyAction::VTS_EMAIL_PROPERTY;
    const FIELD_EMAIL_MAX_LENGTH = 255;

    const EMAIL_ADDRESS_INVALID_MSG = 'you must enter a valid email address';
    const EMAIL_ADDRESS_TOO_LONG_MSG = 'must be %max% characters or less';

    private $emailElement;

    public function __construct()
    {
        parent::__construct(self::FIELD_EMAIL);

        $this->emailElement = new Text();
        $this->emailElement->setName(self::FIELD_EMAIL)
            ->setLabel('Email address')
            ->setAttribute('id', 'email')
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group-compound');

        $this->add($this->emailElement);

        $emailFilter = new Input($this->emailElement->getName());

        $emailInvalidValidator = (new EmailAddressValidator())->setMessage(self::EMAIL_ADDRESS_INVALID_MSG);
        $emailEmptyValidator = (new StringLength())
            ->setMax(self::FIELD_EMAIL_MAX_LENGTH)
            ->setMessage(self::EMAIL_ADDRESS_TOO_LONG_MSG, StringLength::TOO_LONG);

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
