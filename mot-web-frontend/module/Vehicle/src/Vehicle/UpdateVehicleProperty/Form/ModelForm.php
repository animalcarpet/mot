<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\ApiClient\Vehicle\Dictionary\Dto\ModelDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Zend\Form\Element\Select;
use Zend\InputFilter\Input;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class ModelForm extends OtherModelForm
{
    const OTHER_ID = 'other';
    const OTHER_NAME = 'OTHER';
    const FIELD_MODEL_NAME = 'vehicleModel';
    const FIELD_MODEL_ID = 'vehicleModel';
    const DATA_TARGET_ID = 'other-model-field';
    const FIELD_OTHER_MODEL_LABEL = 'If other, please specify';

    /**
     * @var Select
     */
    private $modelElement;

    public function getModelElement()
    {
        return $this->modelElement;
    }

    public function getSelectedModelName()
    {
        $options = $this->getModelElement()->getValueOptions();

        return $options[$this->getModelElement()->getValue()];
    }

    /**
     * @param ModelDto[] $modelList
     */
    public function __construct(array $modelList, $makeName = '')
    {
        parent::__construct(false);

        TypeCheck::assertCollectionOfClass($modelList, ModelDto::class);

        $modelOther = new ModelDto();
        $modelOther
            ->setId(self::OTHER_ID)
            ->setCode(self::OTHER_ID)
            ->setName(self::OTHER_NAME);

        $modelList[] = $modelOther;
        $modelSelectValue = ArrayUtils::mapWithKeys(
            $modelList,
            function ($key, ModelDto $model) {
                return $model->getId();
            },
            function ($key, ModelDto $model) {
                return $model->getName();
            }
        );

        $this->createModelElement($modelSelectValue, $makeName);
        $this->createModelValidator($modelSelectValue);
    }

    public function isValid()
    {
        if ($this->getModelElement()->getValue() === self::OTHER_ID) {
            $this->createOtherModelValidator(true);
        } else {
            $this->getOtherModelElement()->setValue(null);
        }

        return parent::isValid();
    }

    private function createModelElement(array $modelSelectValue, $makeName)
    {
        $this->modelElement = new Select();

        $this->modelElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($modelSelectValue)
            ->setName(self::FIELD_MODEL_NAME)
            ->setLabel('Model')
            ->setAttribute('help', sprintf('Choose a %s model', $makeName))
            ->setAttribute('id', self::FIELD_MODEL_ID)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('data-target-value', self::OTHER_ID)
            ->setAttribute('data-target', self::DATA_TARGET_ID);

        $this->add($this->modelElement);
    }

    private function createModelValidator(array $modelSelectValue)
    {
        $notEmptyValidator = (new NotEmpty())->setMessage(' you must choose a model', NotEmpty::IS_EMPTY);
        $inArrayValidator = (new InArray())
            ->setHaystack(array_keys($modelSelectValue));

        $inArrayValidator->setMessage(' you must choose a model');
        $makeInput = new Input(self::FIELD_MODEL_ID);
        $makeInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator)
            ->attach($inArrayValidator);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add($makeInput);
        $this->setInputFilter($inputFilter);
    }
}
