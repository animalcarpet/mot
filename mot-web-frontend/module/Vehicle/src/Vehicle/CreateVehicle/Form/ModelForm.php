<?php

namespace Vehicle\CreateVehicle\Form;

use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class ModelForm extends Form
{
    const MODEL = 'vehicleModel';
    const OTHER = 'Other';

    const LABEL_PLEASE_SELECT = 'Please select';
    const LABEL_OTHER = 'Other';
    const OTHER_MAX_LENGTH = 39;

    const MODEL_EMPTY_MSG = 'Model - select an option';
    const MODEL_EMPTY_FIELD_MSG = 'Select a model';
    const OTHER_EMPTY_MSG = 'Model, if other - enter the model';
    const OTHER_EMPTY_FIELD_MSG = 'Enter the model';

    const ERROR_FIELD_TOO_LONG = 'Must be shorter than 40 characters';
    const ERROR_MESSAGE_TOO_LONG = 'Model, if other - must be shorter than 40 characters';

    private $errorMessages = [];

    public function __construct(array $models, $selectedMake = null, $other = null)
    {
        parent::__construct();
        $models = $this->formatModelsForDisplay($models);

        $this->add((new Select())
            ->setName(self::MODEL)
            ->setValueOptions($models)
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
            ->setAttribute('id', 'other-model')
            ->setAttribute('required', true)
            ->setAttribute('group', true)
        );
    }

    public function isValid()
    {
        $isValid = parent::isValid();
        $fieldsValid = true;

        if ($this->getModel()->getValue() === self::LABEL_PLEASE_SELECT) {
            $this->addErrorMessage(self::MODEL_EMPTY_MSG);
            $this->addLabelError($this->getModel(), [self::MODEL_EMPTY_MSG]);
            $fieldsValid = false;
        }

        if ($this->getModel()->getValue() === self::LABEL_OTHER && empty($this->getOther()->getValue())) {
            $this->addErrorMessage(self::OTHER_EMPTY_MSG);
            $this->addLabelError($this->getOther(), [self::OTHER_EMPTY_FIELD_MSG]);
            $fieldsValid = false;
        }

        if (!empty($this->getOther()->getValue()) && $this->getModel()->getValue() === self::OTHER &&
            strlen($this->getOther()->getValue()) > self::OTHER_MAX_LENGTH) {
            $this->addErrorMessage(self::ERROR_MESSAGE_TOO_LONG);
            $this->addLabelError($this->getOther(), [self::ERROR_FIELD_TOO_LONG]);
            $fieldsValid = false;
        }

        return $isValid && $fieldsValid;
    }

    public function getModel()
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

    public function addLabelError(ElementInterface $field, $errors)
    {
        $field->setMessages($errors);
    }

    private function formatModelsForDisplay(array $makes)
    {
        $options = [];
        $options [self::LABEL_PLEASE_SELECT] = self::LABEL_PLEASE_SELECT;
        foreach ($makes as $s) {
            $options[$s['id']] = $s['name'];
        }
        $options [self::LABEL_OTHER] = self::LABEL_OTHER;

        return $options;
    }
}
