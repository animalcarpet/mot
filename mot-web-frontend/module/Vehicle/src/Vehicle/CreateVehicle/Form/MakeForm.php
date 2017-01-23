<?php

namespace Vehicle\CreateVehicle\Form;

use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class MakeForm extends Form
{
    const MODEL = 'vehicleMake';
    const OTHER = 'Other';

    const LABEL_OTHER_MAX_LENGTH = 39;

    const LABEL_PLEASE_SELECT = 'Please select';
    const ERROR_MESSAGE_INVALID_OTHER_ENTRY = 'Make, if other - enter the make';
    const ERROR_MESSAGE_EMPTY_SELECTION = 'Make - select an option';
    const ERROR_FIELD_EMPTY = 'Enter the make';

    const ERROR_FIELD_TOO_LONG = 'Must be shorter than 40 characters';
    const ERROR_MESSAGE_TOO_LONG = 'Make, if other - must be shorter than 40 characters';

    private $errorMessages = [];

    public function __construct(array $makes, $selectedMake = null, $other = null)
    {
        parent::__construct();
        $makes = $this->formatMakesForDisplay($makes);

        $this->add((new Select())
            ->setName(self::MODEL)
            ->setValueOptions($makes)
            ->setValue($selectedMake)
            ->setOption('label_attributes', ['class' => 'block-label'])
            ->setAttribute('class', 'form-control form-control-1-2')
            ->setAttribute('id', self::MODEL)
            ->setAttribute('required', true)
            ->setAttribute('group', true)
            ->setAttribute('data-target-value', self::OTHER)
            ->setAttribute('data-target', 'other-wrapper')
        );

        $this->add((new Text())
            ->setName(self::OTHER)
            ->setValue($other)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'other-make')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        $fieldsValid = true;

        if ($this->getVehicleMake()->getValue() === self::LABEL_PLEASE_SELECT) {
            $this->addErrorMessage(self::ERROR_MESSAGE_EMPTY_SELECTION);
            $this->addLabelError($this->getVehicleMake(), [self::ERROR_FIELD_EMPTY]);
            $fieldsValid = false;
        }

        if ($this->getVehicleMake()->getValue() === self::OTHER && empty($this->getOther()->getValue())) {
            $this->addErrorMessage(self::ERROR_MESSAGE_INVALID_OTHER_ENTRY);
            $this->addLabelError($this->getOther(), [self::ERROR_FIELD_EMPTY]);
            $fieldsValid = false;
        }

        if (!empty($this->getOther()->getValue()) && $this->getVehicleMake()->getValue() === self::OTHER &&
            strlen($this->getOther()->getValue()) > self::LABEL_OTHER_MAX_LENGTH) {
            $this->addErrorMessage(self::ERROR_MESSAGE_TOO_LONG);
            $this->addLabelError($this->getOther(), [self::ERROR_FIELD_TOO_LONG]);
            $fieldsValid = false;
        }

        return $isValid && $fieldsValid;
    }

    public function addLabelError(ElementInterface $field, $errors)
    {
        $field->setMessages($errors);
    }

    public function getVehicleMake()
    {
        return $this->get(self::MODEL);
    }

    public function getOther()
    {
        return $this->get(self::OTHER);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function addErrorMessage($message)
    {
        array_push($this->errorMessages, $message);
    }

    private function formatMakesForDisplay(array $makes)
    {
        $options = [];
        $options [self::LABEL_PLEASE_SELECT] = self::LABEL_PLEASE_SELECT;
        foreach ($makes as $make) {
            $options[$make['id']] = $make['name'];
        }
        $options [self::OTHER] = self::OTHER;

        return $options;
    }
}