<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class RegisteredPhonePropertyForm extends Form
{
    const FIELD_PHONE = UpdateAePropertyAction::AE_REGISTERED_TELEPHONE_PROPERTY;
    const FIELD_PHONE_MAX_LENGTH = 24;

    const PHONE_EMPTY_MSG = 'you must enter a telephone number';
    const PHONE_TOO_LONG_MSG = 'must be %max% characters or less';

    private $phoneElement;

    public function __construct()
    {
        parent::__construct(static::FIELD_PHONE);

        $this->phoneElement = new Text();
        $this
            ->phoneElement
            ->setName(static::FIELD_PHONE)
            ->setLabel('Telephone number')
            ->setAttribute('id', 'phoneTextBox')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->phoneElement);

        $filter = new InputFilter();

        $nameEmptyValidator = (new NotEmpty())->setMessage(static::PHONE_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $nameLengthValidator = (new StringLength())
            ->setMax(static::FIELD_PHONE_MAX_LENGTH)
            ->setMessage(static::PHONE_TOO_LONG_MSG, StringLength::TOO_LONG);

        $nameInput = new Input(static::FIELD_PHONE);
        $nameInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($nameEmptyValidator)
            ->attach($nameLengthValidator);

        $filter->add($nameInput);
        $this->setInputFilter($filter);
    }

    public function getPhoneElement()
    {
        return $this->phoneElement;
    }
}
