<?php

namespace DvsaMotTest\NewVehicle\Form;

use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use DvsaMotTest\NewVehicle\Fieldset\CreateVehicleStepTwoFieldset;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\NotEmpty;

class CreateVehicleStepTwoForm extends Form
{
    const FORM_LEGEND = 'Vehicle specification';

    private $fieldset;

    public function __construct($options = [])
    {
        $name = 'vehicleForm';
        parent::__construct($name, $options);

        $vehicleForm = new CreateVehicleStepTwoFieldset($name, $options);
        $this->fieldset = $vehicleForm;
        $vehicleForm->setUseAsBaseFieldset(true);

        $vehicleForm->setOptions(
            [
                'legend' => self::FORM_LEGEND
            ]
        );

        $this
            ->add($vehicleForm)
            ->add(
                [
                    'name'       => 'submit',
                    'attributes' => [
                        'type'  => 'submit',
                        'value' => 'Review new vehicle record',
                        'id'    => 'submit-button',
                        'class' => 'btn btn-primary'
                    ]
                ]
            )
            ->add(
                [
                    'name'       => 'back',
                    'attributes' => [
                        'type'  => 'submit',
                        'id'    => 'back-link',
                        'value' => 'Back',
                        'class' => 'btn btn-link'
                    ]
                ]
            );
    }

    public function getModel()
    {
        return $this->fieldset->get('model');
    }

    public function getModelOther()
    {
        return $this->fieldset->get('modelOther');
    }

    public function getFuelType()
    {
        return $this->fieldset->get('fuelType');
    }

    public function getVehicleClass()
    {
        return $this->fieldset->get('vehicleClass');
    }

    public function getCylinderCapacity()
    {
        return $this->fieldset->get('cylinderCapacity');
    }

    public function getColour()
    {
        return $this->fieldset->get('colour');
    }

    public function getSecondaryColour()
    {
        return $this->fieldset->get('secondaryColour');
    }

    public function isValid()
    {
        $isValid = parent::isValid();

        $isValid = $this->validateOtherModelEmpty() && $isValid;
        $isValid = $this->validateOtherModelNotEmpty() && $isValid;

        return $isValid;
    }

    private function validateOtherModelEmpty()
    {
        if ($this->getModel()->getValue() == CreateVehicleStepTwoFieldset::LABEL_OTHER_KEY
            && !$this->getModelOther()->getValue())
        {
            $messages = $this->getModelOther()->getMessages();
            $messages[NotEmpty::IS_EMPTY] = CreateVehicleErrors::MODEL_OTHER_EMPTY;
            $this->getModelOther()->setMessages($messages);

            return false;
        }

        return true;
    }

    private function validateOtherModelNotEmpty()
    {
        if ($this->getModel()->getValue() != CreateVehicleStepTwoFieldset::LABEL_OTHER_KEY
            && $this->getModel()->getValue()
            && $this->getModelOther()->getValue())
        {
            $messages = $this->getModelOther()->getMessages();
            $messages[NotEmpty::IS_EMPTY] = CreateVehicleErrors::MODEL_OTHER_EMPTY;
            $this->getModelOther()->setMessages($messages);

            return false;
        }

        return true;
    }
}
