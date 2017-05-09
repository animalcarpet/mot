<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class MakeForm extends Form
{
    const OTHER_ID = 'other';
    const OTHER_NAME = 'OTHER';
    const FIELD_MAKE_NAME = 'vehicleMake';
    const FIELD_MAKE_ID = 'vehicleMake';
    const FIELD_OTHER_MAKE_NAME = 'otherMake';
    const FIELD_OTHER_MAKE_ID = 'otherMake';
    const DATA_TARGET_ID = 'other-make-field';
    const MAX_OTHER_MAKE_LENGTH = 39;
    const FIELD_OTHER_MAKE_LABEL = 'If other, please specify';

    /**
     * @var Select
     */
    private $makeElement;

    /**
     * @var Text
     */
    private $makeOtherElement;

    public function getMakeElement()
    {
        return $this->makeElement;
    }

    public function getSelectedMakeName()
    {
        $options = $this->getMakeElement()->getValueOptions();

        return $options[$this->getMakeElement()->getValue()];
    }

    public function getOtherMakeElement()
    {
        return $this->makeOtherElement;
    }

    /**
     * @param MakeDto[] $modelList
     */
    public function __construct(array $modelList)
    {
        parent::__construct();

        TypeCheck::assertCollectionOfClass($modelList, MakeDto::class);

        $makeOther = new MakeDto();
        $makeOther
            ->setId(self::OTHER_ID)
            ->setCode(self::OTHER_ID)
            ->setName(self::OTHER_NAME)
        ;

        $modelList[] = $makeOther;

        $makeSelectValue = ArrayUtils::mapWithKeys(
            $modelList,
            function ($key, MakeDto $make) {
                return $make->getId();
            },
            function ($key, MakeDto $make) {
                return $make->getName();
            }
        );

        $this->createMakeElement($makeSelectValue);
        $this->createMakeValidator($makeSelectValue);
        $this->createOtherMakeElement();
    }

    public function isValid()
    {
        if ($this->getMakeElement()->getValue() === self::OTHER_ID) {
            $this->createOtherMakeValidator();
        } else {
            $this->getOtherMakeElement()->setValue(null);
        }

        return parent::isValid();
    }

    private function createMakeElement(array $makeSelectValue)
    {
        $this->makeElement = new Select();

        $this->makeElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($makeSelectValue)
            ->setName(self::FIELD_MAKE_NAME)
            ->setLabel('Make')
            ->setAttribute('id', self::FIELD_MAKE_ID)
            ->setAttribute('required', false)
            ->setAttribute('group', true)
            ->setAttribute('data-target-value', self::OTHER_ID)
            ->setAttribute('data-target', self::DATA_TARGET_ID)
        ;

        $this->add($this->makeElement);
    }

    private function createMakeValidator(array $makeSelectValue)
    {
        $notEmptyValidator = (new NotEmpty())->setMessage(' you must choose a make', NotEmpty::IS_EMPTY);
        $inArrayValidator = (new InArray())
            ->setHaystack(array_keys($makeSelectValue));

        $inArrayValidator->setMessage(' you must choose a make');
        $makeInput = new Input(self::FIELD_MAKE_ID);
        $makeInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator)
            ->attach($inArrayValidator)
        ;

        $inputFilter = $this->getInputFilter();
        $inputFilter->add($makeInput);

        $this->setInputFilter($inputFilter);
    }

    private function createOtherMakeElement()
    {
        $this->makeOtherElement = new Text();

        $this->makeOtherElement
            ->setName(self::FIELD_OTHER_MAKE_NAME)
            ->setLabel('Make')
            ->setAttribute('id', self::FIELD_OTHER_MAKE_ID)
            ->setAttribute('required', false)
        ;

        $this->add($this->makeOtherElement);
    }

    private function createOtherMakeValidator()
    {
        $notEmptyValidator = (new NotEmpty())->setMessage(' Enter a name for the unknown make', NotEmpty::IS_EMPTY);
        $stringLengthValidator = new StringLength();
        $stringLengthValidator->setMax(self::MAX_OTHER_MAKE_LENGTH);
        $stringLengthValidator->setMessage('Must be shorter than 40 characters');

        $input = new Input(self::FIELD_OTHER_MAKE_ID);
        $input
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator)
            ->attach($stringLengthValidator);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add($input);
        $this->setInputFilter($inputFilter);
    }
}
