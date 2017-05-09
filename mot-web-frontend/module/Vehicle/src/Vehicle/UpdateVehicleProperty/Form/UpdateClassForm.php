<?php

namespace Vehicle\UpdateVehicleProperty\Form;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\VehicleClassId;
use Zend\Form\Element\Radio;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\ValidatorChain;

class UpdateClassForm extends Form
{
    const FIELD_CLASS = 'class';

    public function __construct()
    {
        parent::__construct();

        $this->add($this->buildClassElement());

        $this->setInputFilter($this->buildInputFilter());
    }

    private function buildClassElement()
    {
        $class = new Radio();
        $class->setName(self::FIELD_CLASS);
        $class->setLabel('Change MOT test class');

        $classes = array_combine(VehicleClassId::getAll(), VehicleClassCode::getAll());

        $valueOptions = [];
        foreach ($classes as $classId => $classCode) {
            $valueOptions[] = [
                'value' => $classId,
                'inputName' => self::FIELD_CLASS,
                'key' => 'Class '.$classCode,
                'attributes' => [
                    'id' => 'class-'.$classCode,
                ],
            ];
        }

        $class->setValueOptions($valueOptions);
        $class->setAttribute('group', true);

        return $class;
    }

    /**
     * @return Radio
     */
    public function getClassElement()
    {
        return $this->get(self::FIELD_CLASS);
    }

    private function buildInputFilter()
    {
        $inArrayValidator = new InArray();
        $values = VehicleClassId::getAll();

        $inArrayValidator->setHaystack($values)
            ->setMessages([
                InArray::NOT_IN_ARRAY => 'Please select MOT test class',
            ]);

        $inputFilter = new InputFilter();
        $inputFilter->add(
            (new Input(self::FIELD_CLASS))->setRequired(true)->setValidatorChain(
                (new ValidatorChain())->attach($inArrayValidator)
            )
        );

        return $inputFilter;
    }
}
