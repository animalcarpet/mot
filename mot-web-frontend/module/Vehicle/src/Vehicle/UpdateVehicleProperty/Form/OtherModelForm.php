<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class OtherModelForm extends Form
{
    const FIELD_OTHER_MODEL_NAME = 'otherModel';
    const FIELD_OTHER_MODEL_ID = 'otherModel';
    const MAX_OTHER_MODEL_LENGTH = 39;

    private $modelOtherElement;

    /**
     * @return Text
     */
    public function getOtherModelElement()
    {
        return $this->modelOtherElement;
    }

    public function getSelectedModelName()
    {
        return ModelForm::FIELD_OTHER_MODEL_NAME;
    }

    public function __construct($isElementRequired = true)
    {
        parent::__construct();

        $this->createOtherModelElement($isElementRequired);
        $this->createOtherModelValidator($isElementRequired);
    }

    protected function createOtherModelElement($required)
    {
        $this->modelOtherElement = new Text();

        $this->modelOtherElement
            ->setName(self::FIELD_OTHER_MODEL_NAME)
            ->setLabel("Model")
            ->setAttribute('id', self::FIELD_OTHER_MODEL_ID)
            ->setAttribute('required', $required);

        $this->add($this->modelOtherElement);
    }

    protected function createOtherModelValidator($isElementRequired)
    {
        $notEmptyValidator = (new NotEmpty())->setMessage(" Enter the model name of the vehicle", NotEmpty::IS_EMPTY);
        $stringLengthValidator = new StringLength();
        $stringLengthValidator->setMax(self::MAX_OTHER_MODEL_LENGTH);
        $stringLengthValidator->setMessage("Must be shorter than 40 characters");

        $input = new Input(self::FIELD_OTHER_MODEL_ID);
        $input
            ->setRequired($isElementRequired)
            ->getValidatorChain()
            ->attach($notEmptyValidator)
            ->attach($stringLengthValidator);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add($input);
        $this->setInputFilter($inputFilter);
    }
}
