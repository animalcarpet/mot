<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form;

use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidationCallback;
use Dvsa\Mot\Frontend\SecurityCardModule\Validator\SecurityCardPinValidator;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class SecurityCardValidationForm extends Form
{
    const PIN = 'pin';

    public function __construct(SecurityCardPinValidationCallback $validationCallback = null)
    {
        parent::__construct();

        $this->add((new Text())
            ->setName(self::PIN)

            ->setLabel('Security card PIN')
            ->setAttribute('id', self::PIN)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('divModifier', 'form-group')
            ->setAttribute('inputModifier', '1-4')
            ->setAttribute('type', 'text')
            ->setAttribute('autoCompleteOff', true)
        );

        $filter = new InputFilter();

        $pinInput = new Input(self::PIN);
        $pinInput->setContinueIfEmpty(true)->getValidatorChain()
            ->attach((new SecurityCardPinValidator())->setValidationCallback($validationCallback));
        $filter->add($pinInput);


        $this->setInputFilter($filter);
    }

    public function getPinField()
    {
        return $this->get(self::PIN);
    }

    /**
     * @param $field
     * @param $error
     */
    public function setCustomError($field, $error)
    {

        $field->setMessages([$error]);
    }
}