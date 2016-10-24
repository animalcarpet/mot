<?php
namespace Vehicle\UpdateVehicleProperty\Form\InputFilter;

use Vehicle\UpdateVehicleProperty\Form\UpdateColourForm;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class UpdateColourInputFilter extends InputFilter
{
    const COLOUR_EMPTY_MSG = "select a colour";
    private $colourOptions;
    private $secondaryColourOptions;

    public function __construct(array $colourOptions, array $secondaryColourOptions)
    {
        $this->colourOptions = $colourOptions;
        $this->secondaryColourOptions = $secondaryColourOptions;

        $this->buildInputs();
    }

    protected function buildInputs()
    {
        $this->add($this->createColourInput(
            UpdateColourForm::FIELD_COLOUR,
            $this->colourOptions,
            true
        ));

        $this->add($this->createColourInput(
            UpdateColourForm::FIELD_SECONDARY_COLOUR,
            $this->secondaryColourOptions,
            false
        ));
    }

    private function createColourInput($field, $colourOptions, $required)
    {
        $input = new Input($field);
        $input->setRequired($required);

        $inArrayValidator = $this->buildInArrayValidator($colourOptions, self::COLOUR_EMPTY_MSG);
        $input->getValidatorChain()
            ->attach($inArrayValidator);

        if($required) {
            $notEmptyValidator = (new NotEmpty())
                ->setMessage(self::COLOUR_EMPTY_MSG, NotEmpty::IS_EMPTY);

            $input->getValidatorChain()->attach($notEmptyValidator);
        }

        return $input;
    }

    private function buildInArrayValidator($colourOptions, $message)
    {
        $inArrayValidator = (new InArray())
            ->setHaystack(array_keys($colourOptions))
            ->setMessage($message, InArray::NOT_IN_ARRAY);

        return $inArrayValidator;
    }
}