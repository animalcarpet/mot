<?php
namespace DvsaMotTest\NewVehicle\Form;

use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use DvsaMotTest\NewVehicle\Fieldset\CreateVehicleStepOneFieldset;
use Zend\Form\Element\DateSelect;
use Zend\Form\Form;
use Zend\Validator\NotEmpty;

class CreateVehicleStepOneForm extends Form
{
    const FORM_LEGEND = 'Vehicle identification';

    private $fieldset;

    public function __construct($options = [])
    {
        $name = 'vehicleForm';
        parent::__construct($name, $options);

        $this->fieldset = new CreateVehicleStepOneFieldset($name, $options);
        $this->fieldset->setUseAsBaseFieldset(true);

        $this->fieldset->setOptions(
            [
                'legend' => self::FORM_LEGEND
            ]
        );

        $this->add($this->fieldset)
            ->add(
                [
                    'name' => 'submit',
                    'attributes' => [
                        'type' => 'submit',
                        'value' => 'Submit',
                        'id' => 'submit-button',
                        'class' => 'btn btn-primary'
                    ]
                ]
            );
        
        $this->getTransmissionType()->setAttribute("class", "");
    }

    public function setData($data)
    {
        $vehicleForm = $data['vehicleForm'];
        if (!empty($vehicleForm)) {
            $vehicleForm['registrationNumber'] = mb_strtoupper(preg_replace('/\s+/', '',$vehicleForm['registrationNumber']));
            $data['vehicleForm'] = $vehicleForm;
        }

        return parent::setData($data);
    }

    public function getFromFieldset($fieldName)
    {
        return $this->fieldset->get($fieldName);
    }

    public function getRegistrationNumber()
    {
        return $this->fieldset->get('registrationNumber');
    }

    public function getVin()
    {
        return $this->fieldset->get('VIN');
    }

    public function getEmptyVrmReason()
    {
        return $this->fieldset->get('emptyVrmReason');
    }

    public function getEmptyVinReason()
    {
        return $this->fieldset->get('emptyVinReason');
    }

    public function getMake()
    {
        return $this->fieldset->get('make');
    }

    public function getMakeOther()
    {
        return $this->fieldset->get('makeOther');
    }

    /**
     * @return DateSelect
     */
    public function getDateOfFirstUse()
    {
        return $this->fieldset->get('dateOfFirstUse');
    }

    public function getCountryOfRegistration()
    {
        return $this->fieldset->get('countryOfRegistration');
    }

    public function getTransmissionType()
    {
        return $this->fieldset->get('transmissionType');
    }

    public function isValid()
    {
        $isValid = parent::isValid();

        $isValid = $this->validateOtherMakeEmpty() && $isValid;
        $isValid = $this->validateOtherMakeNotEmpty() && $isValid;

        return $isValid;
    }

    private function validateOtherMakeEmpty()
    {
        if ($this->getMake()->getValue() == CreateVehicleStepOneFieldset::LABEL_OTHER_KEY
            && !$this->getMakeOther()->getValue()
        ) {
            $messages = $this->getMakeOther()->getMessages();
            $messages[NotEmpty::IS_EMPTY] = CreateVehicleErrors::MAKE_OTHER_EMPTY;
            $this->getMakeOther()->setMessages($messages);

            return false;
        }

        return true;
    }

    private function validateOtherMakeNotEmpty()
    {
        if ($this->getMake()->getValue() != CreateVehicleStepOneFieldset::LABEL_OTHER_KEY
            && $this->getMake()->getValue()
            && $this->getMakeOther()->getValue()
        ) {
            $messages = $this->getMakeOther()->getMessages();
            $messages[NotEmpty::IS_EMPTY] = CreateVehicleErrors::MAKE_OTHER;
            $this->getMakeOther()->setMessages($messages);

            return false;
        }

        return true;
    }
}
