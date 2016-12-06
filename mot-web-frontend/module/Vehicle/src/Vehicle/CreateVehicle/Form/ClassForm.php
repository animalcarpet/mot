<?php

namespace Vehicle\CreateVehicle\Form;

use Zend\Form\Form;
use Zend\Form\Element\Radio;
use Zend\Form\ElementInterface;
use DvsaCommon\Enum\VehicleClassId;
use DvsaCommon\Enum\VehicleClassCode;

class ClassForm extends Form
{
    const FIELD_CLASS = "class";
    const SELECT_CLASS_ERROR = 'Test class - select an option';

    private $errorMessages = [];

    private $allowedClasses = [];

    public function __construct($selectedClassId = null, $allowedClasses = [])
    {
        parent::__construct();

        $this->allowedClasses = $allowedClasses;

        $this->add((new Radio())
            ->setName(self::FIELD_CLASS)
            ->setLabel('Test class')
            ->setValueOptions($this->getClassValueOptions($selectedClassId))
            ->setAttribute('group', true));
    }

    private function getClassValueOptions($selectedId = null)
    {
        $classes = array_combine(VehicleClassId::getAll(), VehicleClassCode::getAll());
        $valueOptions = [];
        foreach ($classes as $classId => $classCode) {
            $valueOptions[] = [
                'value'      => $classId,
                'inputName'  => self::FIELD_CLASS,
                'key'        => 'Class ' . $classCode,
                'label'      => 'Class ' . $classCode,
                'selected'   => ($classId == $selectedId),
                'attributes' => ['id' => 'testClass' . $classCode],
                'label_attributes' => ['class' => 'block-label'],
            ];
        }
        return $valueOptions;
    }

    public function isValid()
    {
        $selectedValue = $this->getClassRadioGroup()->getValue();

        if (empty($this->getClassRadioGroup()->getValue()) ||
            !in_array($selectedValue, VehicleClassCode::getAll())) {
            $this->addErrorMessage(self::SELECT_CLASS_ERROR);
            $this->addLabelError($this->getClassRadioGroup(), ['Select a test class']);
            return false;
        }

        if (!in_array($selectedValue, $this->allowedClasses['forPerson'])) {
            $this->addErrorMessage('Test class - you are not eligible to test class ' . $selectedValue . ' vehicles');
            $this->addLabelError($this->getClassRadioGroup(), ['You are not eligible to test class ' . $selectedValue . ' vehicles']);
            return false;
        }

        if (!in_array($selectedValue, $this->allowedClasses['forVts'])) {
            $this->addErrorMessage('Test class - this VTS is not eligible to test class ' . $selectedValue . ' vehicles');
            $this->addLabelError($this->getClassRadioGroup(), ['This VTS is not eligible to test class ' . $selectedValue . ' vehicles']);
            return false;
        }

        return true;
    }

    public function getClassRadioGroup()
    {
        return $this->get(self::FIELD_CLASS);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    private function addErrorMessage($errorMessage)
    {
        array_push($this->errorMessages, $errorMessage);
    }

    public function addLabelError(ElementInterface $field, $errors)
    {
        $field->setMessages($errors);
    }
}