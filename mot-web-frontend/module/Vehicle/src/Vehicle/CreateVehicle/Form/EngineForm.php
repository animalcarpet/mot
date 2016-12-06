<?php

namespace Vehicle\CreateVehicle\Form;

use DvsaCommon\Model\FuelType;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\ElementInterface;
use Zend\Form\Form;
use Zend\Validator\Regex;

class EngineForm extends Form
{
    const FIELD_FUEL_TYPE = 'fuel-type';
    const FIELD_CAPACITY = 'cylinder-capacity';

    const ERROR_FUEL_TYPE_REQUIRED = 'Select a fuel type';
    const ERROR_CAPACITY_REQUIRED = 'Enter a value';
    const ERROR_CAPACITY_MUST_BE_NUMERIC = 'Can only contain numbers';
    const ERROR_CAPACITY_MUST_BE_SHORTER_THAN_SIX_DIGITS = 'Must be shorter than 6 digits';
    const PLEASE_SELECT_TEXT = 'Please select';

    const CAPACITY_MIN_VALUE = 0;
    const CAPACITY_MAX_LENGTH = 5;

    private $errorMessages = [];

    /**
     * @var Select
     */
    private $engineType;

    /**
     * @var Text
     */
    private $engineCapacity;

    public function __construct($fuelTypes, $engineDataToPrePopulate)
    {
        parent::__construct('updateEngine');
        $this->addElements($this->orderFuelTypes($fuelTypes), $engineDataToPrePopulate);
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return Select
     */
    public function getEngineTypeElement()
    {
        return $this->get(self::FIELD_FUEL_TYPE);
    }

    /**
     * @return Text
     */
    public function getEngineCapacityElement()
    {
        return $this->get(self::FIELD_CAPACITY);
    }

    public function isValid()
    {
        $engineTypeElement = $this->getEngineTypeElement();
        $fuelType = $engineTypeElement->getValue();

        $isValid = $this->checkIfFuelTypeIsSelected($engineTypeElement, $fuelType);

        if ($isValid && FuelTypeAndCylinderCapacity::isCylinderCapacityCompulsoryForFuelTypeCode($fuelType)) {
            $isValid = $this->checkIfCapacityFieldIsValid();
        }

        return $isValid;
    }

    private function checkIfFuelTypeIsSelected($engineTypeElement, $fuelType)
    {
        $isValid = true;

        if (empty($fuelType)) {
            $this->addErrorMessage('Fuel type - select an option');
            $this->setCustomError($engineTypeElement, self::ERROR_FUEL_TYPE_REQUIRED);
            $this->showLabelOnError(self::FIELD_FUEL_TYPE, 'Fuel type');

            $isValid = false;
        }

        return $isValid;
    }

    private function onlyContainsNumbers()
    {
        $isValid = true;

        return $isValid;
    }

    private function checkIfCapacityFieldIsValid()
    {
        $capacityTypeElement = $this->getEngineCapacityElement();
        $capacity = $this->getEngineCapacityElement()->getValue();
        $isValid = true;

        if (empty($capacity)) {
            $this->addErrorMessage('Cylinder capacity - enter a value');
            $this->setCustomError($capacityTypeElement, self::ERROR_CAPACITY_REQUIRED);
            $this->showLabelOnError(self::FIELD_CAPACITY, 'Cylinder capacity');

            $isValid = false;
        }

        if ((strlen($capacity) > self::CAPACITY_MAX_LENGTH)) {
            $this->addErrorMessage('Cylinder capacity - must be shorter than 6 digits');
            $this->setCustomError($capacityTypeElement, self::ERROR_CAPACITY_MUST_BE_SHORTER_THAN_SIX_DIGITS);
            $this->showLabelOnError(self::FIELD_CAPACITY, 'Cylinder capacity');

            $isValid = false;
        }

        $onlyNumbersRegex = new Regex(array('pattern' => '/^[0-9]+$/'));

        if (!empty($capacity) && !$onlyNumbersRegex->isValid($capacity)) {
            $this->addErrorMessage('Cylinder capacity - can only contain numbers');
            $this->setCustomError($capacityTypeElement, self::ERROR_CAPACITY_MUST_BE_NUMERIC);
            $this->showLabelOnError(self::FIELD_CAPACITY, 'Cylinder capacity');

            $isValid = false;
        }

        return $isValid;
    }

    private function addElements($options, $engineDataToPrePopulate)
    {
        $selectedEngineType = $engineDataToPrePopulate[self::FIELD_FUEL_TYPE];
        $capacity = $engineDataToPrePopulate[self::FIELD_CAPACITY];

        $this->engineType = new Select();
        $this->engineType
            ->setValueOptions($options)
            ->setValue($selectedEngineType)
            ->setName(self::FIELD_FUEL_TYPE)
            ->setLabel('Fuel type')
            ->setAttribute('class', 'form-control form-control-1-2')
            ->setAttribute('id', self::FIELD_FUEL_TYPE)
            ->setAttribute('type', 'select')
            ->setAttribute('required', true)
            ->setAttribute('data-target', 'div-cylinder-capacity')
            ->setAttribute('data-target-value', FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacityAsString());

        $this->add($this->engineType);

        $this->engineCapacity = new Text();
        $this->engineCapacity
            ->setName(self::FIELD_CAPACITY)
            ->setLabel('Cylinder capacity')
            ->setValue($capacity)
            ->setAttribute('class', 'form-control')
            ->setAttribute('id', 'cylinder-capacity-input')
            ->setAttribute('required', true)
            ->setAttribute('hint', 'Enter a value in cc, for example, 1400');

        $this->add($this->engineCapacity);
    }

    private function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    private function showLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }

    /**
     * @param array $fuelTypes
     * @return array
     */
    private function orderFuelTypes($fuelTypes)
    {
        $out = [];
        $out[''] = self::PLEASE_SELECT_TEXT;
        foreach (FuelType::getOrderedFuelTypeList() as $typeCode) {
            $out[$typeCode] = $fuelTypes[$typeCode];
        }

        return $out;
    }

    private function addErrorMessage($errorMessage)
    {
        array_push($this->errorMessages, $errorMessage);
    }
}