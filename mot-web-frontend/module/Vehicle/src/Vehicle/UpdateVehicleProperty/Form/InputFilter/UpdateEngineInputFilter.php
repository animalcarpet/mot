<?php

namespace Vehicle\UpdateVehicleProperty\Form\InputFilter;

use Vehicle\UpdateVehicleProperty\Form\UpdateEngineForm;
use Zend\Filter\Callback;
use Zend\Filter\StringTrim;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\Between;
use Zend\Validator\Digits;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class UpdateEngineInputFilter implements InputFilterAwareInterface
{
    private $options;

    /**
     * @param array $selectOptions
     */
    public function __construct($selectOptions)
    {
        $this->options = $selectOptions;
    }

    /**
     * Set input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return InputFilterAwareInterface
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \InvalidArgumentException("This shouldn't be used");
    }

    /**
     * Retrieve input filter
     *
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        $typeInArrayValidator = (new InArray())
            ->setHaystack(array_keys($this->options))
            ->setMessage(UpdateEngineForm::FUEL_TYPE_EMPTY_MSG, InArray::NOT_IN_ARRAY);

        $typeEmptyValidator = (new NotEmpty())
            ->setMessage(UpdateEngineForm::FUEL_TYPE_EMPTY_MSG, NotEmpty::IS_EMPTY);

        $typeInput = new Input(UpdateEngineForm::FIELD_FUEL_TYPE);
        $typeInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($typeEmptyValidator)
            ->attach($typeInArrayValidator);

        $capacityInput = new Input(UpdateEngineForm::FIELD_CAPACITY);
        $callback = new Callback(
            function ($value) {
                return $value !== '0' ? ltrim($value, '0') : $value;
            }
        );

        $capacityInput
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->setErrorMessage(UpdateEngineForm::CAPACITY_ENTER_A_NUMBER)
            ->setFilterChain($capacityInput->getFilterChain()
                ->attach(new StringTrim())
                ->attach($callback)
            );

        $filter = new InputFilter();
        $filter->add($capacityInput);
        $filter->add($typeInput);

        return $filter;
    }

    /**
     * @return InputFilter
     */
    public function getEngineCapacityValidator()
    {
        $capacity = new Input(UpdateEngineForm::FIELD_CAPACITY);

        $capacityNotEmpty = (new NotEmpty())
            ->setMessage(UpdateEngineForm::CAPACITY_ENTER_A_NUMBER, NotEmpty::IS_EMPTY);

        $capacityLength = (new StringLength())
            ->setMax(UpdateEngineForm::CAPACITY_MAX_LENGTH)
            ->setMin(UpdateEngineForm::CAPACITY_MIN_LENGTH)
            ->setMessage(UpdateEngineForm::CAPACITY_MAXIMUM_CAPACITY_MESSAGE, StringLength::TOO_LONG);

        $capacityOnlyNumbers = new Digits([
            'messages' => [
                Digits::NOT_DIGITS => UpdateEngineForm::CAPACITY_CAN_ONLY_CONTAIN_NUMBERS,
                Digits::STRING_EMPTY => UpdateEngineForm::CAPACITY_ENTER_A_NUMBER,
                Digits::INVALID => UpdateEngineForm::CAPACITY_CAN_ONLY_CONTAIN_NUMBERS,
            ]
        ]);

        $capacityBetween = new Between([
            'min' => UpdateEngineForm::CAPACITY_MIN_VALUE,
            'max' => UpdateEngineForm::CAPACITY_MAX_VALUE,
            'inclusive' => true,
            'messages' => [
                Between::NOT_BETWEEN => UpdateEngineForm::CAPACITY_MAXIMUM_CAPACITY_MESSAGE,
            ]
        ]);

        $capacity
            ->getValidatorChain()
            ->attach($capacityNotEmpty)
            ->attach($capacityOnlyNumbers)
            ->attach($capacityLength)
            ->attach($capacityBetween);

        $filter = new InputFilter();
        $filter->add($capacity);

        return $filter;
    }
}