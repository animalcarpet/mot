<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class TradingNamePropertyForm extends Form
{
    const FIELD_NAME = UpdateAePropertyAction::AE_TRADING_NAME_PROPERTY;
    const TRADING_NAME_EMPTY_MSG = 'you must enter a trading name';
    const TRADING_NAME_TOO_LONG_MSG = 'must be %max% characters or less';
    const FIELD_NAME_MAX_LENGTH = 60;

    private $nameElement;

    public function __construct()
    {
        parent::__construct(self::FIELD_NAME);

        $this->nameElement = new Text();
        $this
            ->nameElement
            ->setName(self::FIELD_NAME)
            ->setLabel('Trading name')
            ->setAttribute('id', 'aeTradingNameTextBox')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->nameElement);

        $filter = new InputFilter();

        $nameEmptyValidator = (new NotEmpty())->setMessage(self::TRADING_NAME_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $nameLengthValidator = (new StringLength())
            ->setMax(self::FIELD_NAME_MAX_LENGTH)
            ->setMessage(self::TRADING_NAME_TOO_LONG_MSG, StringLength::TOO_LONG);

        $nameInput = new Input(self::FIELD_NAME);
        $nameInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($nameEmptyValidator)
            ->attach($nameLengthValidator);

        $filter->add($nameInput);
        $this->setInputFilter($filter);
    }

    public function getTradingNameElement()
    {
        return $this->nameElement;
    }
}
