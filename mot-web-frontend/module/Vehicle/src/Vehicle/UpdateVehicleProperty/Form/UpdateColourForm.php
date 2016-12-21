<?php
namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Enum\ColourCode;
use Vehicle\UpdateVehicleProperty\Form\InputFilter\UpdateColourInputFilter;
use Zend\Form\Element\Select;
use Zend\Form\Form;

class UpdateColourForm extends Form
{
    const FIELD_COLOUR = "colour";
    const FIELD_SECONDARY_COLOUR = "secondaryColour";

    private $colours;
    private $colourOptions;

    public function __construct(array $colours)
    {
        parent::__construct();

        $this->colours = $colours;
        $this->colourOptions = $this->buildColourOptions($colours);

        $this->add($this->createColourElement());
        $this->add($this->createSecondaryColourElement());
    }

    public function setData($data)
    {
        parent::setData($data);

        $this->setInputFilter($this->buildInputFilter($data[self::FIELD_SECONDARY_COLOUR]));
    }

    private function createColourElement()
    {
        $element = new Select();

        $element
            ->setDisableInArrayValidator(true)
            ->setValueOptions($this->colourOptions)
            ->setName(self::FIELD_COLOUR)
            ->setLabel("Primary colour")
            ->setAttribute('type', 'select')
            ->setAttribute('id', self::FIELD_COLOUR)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('formControlClass', 'form-control-select')
            ->setAttribute('data-target', 'secondaryColours')
            ->setAttribute('data-target-value','S,P,B,A,V,G,H,L,T,K,E,D,C,M,U,N,F,R,W,J')
            ->setAttribute('aria-expanded', false)
            ->setAttribute('aria-controls', 'secondaryColours')
            ->setAttribute('inputModifier', '1-4');

        return $element;
    }

    private function createSecondaryColourElement()
    {
        $element = new Select();

        $element
            ->setDisableInArrayValidator(true)
            ->setValueOptions($this->colourOptions)
            ->setName(self::FIELD_SECONDARY_COLOUR)
            ->setLabel("Secondary colour")
            ->setAttribute("defaultValue", "No other colour")
            ->setAttribute('type', 'select')
            ->setAttribute('id', self::FIELD_SECONDARY_COLOUR)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('formControlClass', 'form-control-select')
            ->setAttribute('inputModifier', '1-4');

        return $element;
    }

    public function getColourElement()
    {
        return $this->get(self::FIELD_COLOUR);
    }

    public function getSecondaryColourElement()
    {
        return $this->get(self::FIELD_SECONDARY_COLOUR);
    }

    private function buildColourOptions($colours)
    {
        unset($colours[ColourCode::NOT_STATED]);
        asort($colours);
        $colours += [ColourCode::NOT_STATED => "Not stated"];

        return $colours;
    }

    private function buildInputFilter($secondaryColourPicked)
    {
        $colourOptions = $this->colourOptions;
        $secondaryColourOptions = $this->colourOptions;

        //if secondary colour is picked then primary colour can't be not stated nor null
        if(!in_array($secondaryColourPicked, [ColourCode::NOT_STATED, ""], true)) {
            unset($colourOptions[ColourCode::NOT_STATED]);
        }

        return new UpdateColourInputFilter(
            $colourOptions,
            $secondaryColourOptions
        );
    }
}