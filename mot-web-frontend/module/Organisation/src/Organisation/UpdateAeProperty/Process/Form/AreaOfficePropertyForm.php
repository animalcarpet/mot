<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class AreaOfficePropertyForm extends Form
{
    const FIELD_AREA_OFFICE = UpdateAePropertyAction::AE_DVSA_AREA_OFFICE_STATUS_PROPERTY;
    const STATUS_EMPTY_MSG = 'you must choose an area office';

    private $areaOfficeElement;

    /**
     * @param array $possibleAreaOffices
     */
    public function __construct(array $possibleAreaOffices)
    {
        parent::__construct(self::FIELD_AREA_OFFICE);

        $this->areaOfficeElement = new Select();
        $this
            ->areaOfficeElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($possibleAreaOffices)
            ->setName(self::FIELD_AREA_OFFICE)
            ->setLabel('Area office')
            ->setAttribute('id', 'aeAreaOfficeSelectSet')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->areaOfficeElement);

        $areaOfficeEmptyValidator = (new NotEmpty())->setMessage(self::STATUS_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $areaOfficeInArrayValidator = (new InArray())
            ->setHaystack($possibleAreaOffices);
        $areaOfficeInArrayValidator->setMessage(' you must choose an area office');
        $areaOfficeInput = new Input(self::FIELD_AREA_OFFICE);
        $areaOfficeInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($areaOfficeEmptyValidator)
            ->attach($areaOfficeInArrayValidator);

        $filter = new InputFilter();
        $filter->add($areaOfficeInput);

        $this->setInputFilter($filter);
    }

    public function getAreaOfficeElement()
    {
        return $this->areaOfficeElement;
    }
}
