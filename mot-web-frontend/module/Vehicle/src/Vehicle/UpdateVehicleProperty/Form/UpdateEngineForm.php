<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Model\FuelType;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterInterface;

class UpdateEngineForm extends Form
{
    const FIELD_FUEL_TYPE = 'fuel-type';
    const FIELD_CAPACITY = 'capacity';
    const FUEL_TYPE_EMPTY_MSG = 'choose a type from the list';
    const CAPACITY_ENTER_A_NUMBER = 'enter a number';
    const CAPACITY_MAXIMUM_CAPACITY_MESSAGE = 'the maximum capacity is 99999';
    const CAPACITY_SHOULD_BE_EMPTY = 'do not enter a capacity for this fuel type';
    const CAPACITY_CAN_ONLY_CONTAIN_NUMBERS = 'the capacity can only contain numbers from 0 to 9';

    const CAPACITY_MIN_VALUE = 0;
    const CAPACITY_MAX_VALUE = 99999;

    const CAPACITY_MAX_LENGTH = 5;
    const CAPACITY_MIN_LENGTH = 1;

    /**
     * @var InputFilterInterface
     */
    private $engineCapacityValidator;

    /**
     * @var Select
     */
    private $engineType;
    /**
     * @var Text
     */
    private $engineCapacity;

    public function __construct($fuelTypes)
    {
        parent::__construct('updateEngine');
        $this->addElements($this->orderFuelTypes($fuelTypes));
    }

    private function addElements($options)
    {
        $this->engineType = new Select();
        $this->engineType
            ->setAttribute('type', 'select')
            ->setValueOptions($options)
            ->setName(self::FIELD_FUEL_TYPE)
            ->setLabel('Fuel type')
            ->setAttribute('id', self::FIELD_FUEL_TYPE)
            ->setAttribute('required', true)
            ->setAttribute('inputModifier', '1-2')
            ->setAttribute('data-target', 'div-cylinder-capacity')
            ->setAttribute('data-target-value', FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacityAsString())
            ->setAttribute('group', true);

        $this->add($this->engineType);

        $this->engineCapacity = new Text();
        $this->engineCapacity
            ->setName(self::FIELD_CAPACITY)
            ->setLabel('Cylinder capacity')
            ->setAttribute('id', 'cylinder-capacity')
            ->setAttribute('required', true)
            ->setAttribute('inputModifier', '1-4')
            ->setAttribute('maxLength', self::CAPACITY_MAX_LENGTH)
            ->setAttribute('help', 'Enter a value in cc, for example, 1400')
            ->setAttribute('group', true);

        $this->add($this->engineCapacity);
    }

    /**
     * @return Select
     */
    public function getEngineTypeElement()
    {
        return $this->engineType;
    }

    /**
     * @return Text
     */
    public function getEngineCapacityElement()
    {
        return $this->engineCapacity;
    }

    public function isValid()
    {
        $valid = parent::isValid();

        if (!$valid) {
            return false;
        }

        $fuelType = $this->getEngineTypeElement()->getValue();

        if (FuelTypeAndCylinderCapacity::isCylinderCapacityOptionalForFuelTypeCode($fuelType)) {
            if ($this->getEngineCapacityElement()->getValue() !== '') {
                $this->getEngineCapacityElement()->setMessages([
                    'elementShouldBeEmpty' => self::CAPACITY_SHOULD_BE_EMPTY,
                ]);
                $valid = false;
            }
        } else {
            $filter = $this->getEngineCapacityValidator();
            $filter->setData($this->getData());
            $valid = $filter->isValid();
            $this->setMessages($filter->getMessages());
        }

        return $valid;
    }

    /**
     * @param array $fuelTypes
     * @return array
     */
    private function orderFuelTypes($fuelTypes)
    {
        $out = [];
        foreach (FuelType::getOrderedFuelTypeList() as $typeCode) {
            $out[$typeCode] = $fuelTypes[$typeCode];
        }

        return $out;
    }

    /**
     * @return InputFilterInterface
     */
    private function getEngineCapacityValidator()
    {
        return $this->engineCapacityValidator;
    }

    /**
     * @param InputFilterInterface $engineCapacityValidator
     * @return UpdateEngineForm
     */
    public function setEngineCapacityValidator(InputFilterInterface $engineCapacityValidator)
    {
        $this->engineCapacityValidator = $engineCapacityValidator;
        return $this;
    }
}