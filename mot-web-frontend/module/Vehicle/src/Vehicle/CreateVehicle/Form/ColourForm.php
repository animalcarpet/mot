<?php

namespace Vehicle\CreateVehicle\Form;

use DvsaCommon\Enum\ColourCode;
use Zend\Form\Element\Select;
use Zend\Form\ElementInterface;
use Zend\Form\Form;

class ColourForm extends Form
{
    const PRIMARY_COLOUR = 'primaryColour';
    const SECONDARY_COLOUR = 'secondaryColours';
    const PLEASE_SELECT_TEXT = 'Please select';
    const PLEASE_SELECT_VALUE = 'PLEASE_SELECT';
    const NO_OTHER_COLOUR_TEXT = 'No other colour';
    const ERROR_PLEASE_SELECT = 'Select an option';

    private $errorMessages = [];

    public function __construct(array $colours, $primaryColour, $secondaryColour)
    {
        parent::__construct();

        $primaryColourValues = $this->getPrimaryColours($colours);
        $secondaryColourValues = $this->getSecondaryColours($colours);

        $this->add((new Select())
            ->setLabel('Primary colour')
            ->setValue($primaryColour)
            ->setValueOptions($primaryColourValues)
            ->setName(self::PRIMARY_COLOUR)
            ->setAttribute('id', self::PRIMARY_COLOUR)
            ->setAttribute('class', ' form-control-select form-control-1-4')
            ->setAttribute('data-target', 'secondaryColours')
            ->setAttribute('data-target-value', $this->getDataTargetValues())
            ->setAttribute('aria-expanded', false)
            ->setAttribute('aria-controls', 'secondaryColours')
        );

        $this->add((new Select())
            ->setLabel('Secondary colour')
            ->setValue($secondaryColour)
            ->setValueOptions($secondaryColourValues)
            ->setName(self::SECONDARY_COLOUR)
            ->setAttribute('id', self::SECONDARY_COLOUR)
            ->setAttribute('class', ' form-control-select form-control-1-4')
        );
    }

    public function isValid()
    {
        $isValid = true;
        $primaryColourValue = $this->getPrimaryColourElement()->getValue();

        if ($primaryColourValue === self::PLEASE_SELECT_VALUE) {
            $this->addErrorMessage('Primary colour - select an option');
            $this->setCustomError($this->getPrimaryColourElement(), self::ERROR_PLEASE_SELECT);
            $this->setLabelOnError(self::PRIMARY_COLOUR, 'Primary colour');

            $isValid = false;
        }

        return $isValid;
    }

    public function setCustomError(ElementInterface $field, $error)
    {
        $field->setMessages([$error]);
    }

    public function getPrimaryColourElement()
    {
        return $this->get(self::PRIMARY_COLOUR);
    }

    public function getSecondaryColourElement()
    {
        return $this->get(self::SECONDARY_COLOUR);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    private function addErrorMessage($errorMessage)
    {
        array_push($this->errorMessages, $errorMessage);
    }

    private function setLabelOnError($field, $label)
    {
        if (count($this->getMessages($field))) {
            $this->getElements()[$field]->setLabel($label);
        }
    }

    private function getPrimaryColours(array $colours)
    {
        $primaryColours = [];
        $primaryColours[self::PLEASE_SELECT_VALUE] = self::PLEASE_SELECT_TEXT;
        foreach (ColourCode::getAll() as $colourCode) {
            if ($colourCode !== ColourCode::NOT_STATED) {
                $primaryColours[$colourCode] = $colours[$colourCode];
            }
        }

        return $primaryColours;
    }

    private function getDataTargetValues()
    {
        $colourCodes = ColourCode::getAll();
        $colourValues = '';
        $colourCodesSize = sizeof($colourCodes);
        $currentValue = 0;

        foreach ($colourCodes as $colour) {
            ++$currentValue;
            if ($currentValue < $colourCodesSize) {
                $colourValues .= $colour.',';
            }

            if ($currentValue == $colourCodesSize) {
                $colourValues .= $colour;
            }
        }

        return $colourValues;
    }

    private function getSecondaryColours(array $colours)
    {
        $secondaryColours = [];
        $secondaryColours['W'] = self::NO_OTHER_COLOUR_TEXT;
        foreach (ColourCode::getAll() as $colourCode) {
            if ($colourCode !== ColourCode::NOT_STATED) {
                $secondaryColours[$colourCode] = $colours[$colourCode];
            }
        }

        return $secondaryColours;
    }
}
