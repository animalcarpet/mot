<?php

namespace Site\UpdateVtsProperty\Process\Form;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\ArrayUtils;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\Form\Element\MultiCheckbox;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class ClassesPropertyForm extends Form
{
    const FIELD_CLASSES = UpdateVtsPropertyAction::VTS_CLASSES_PROPERTY;

    private $classesElement;

    public function __construct()
    {
        parent::__construct(self::FIELD_CLASSES);

        $valueOptions = ArrayUtils::map(VehicleClassCode::getAll(),
            function ($class) {
                return [
                    'value' => $class,
                    'label' => 'Class '.$class,
                    'attributes' => array(
                        'id' => 'class-'.$class,
                    ),
                ];
            });

        $this->classesElement = new MultiCheckbox();
        $this
            ->classesElement
            ->setValueOptions($valueOptions)
            ->setName(self::FIELD_CLASSES)
            ->setLabel('Classes')
            ->setAttribute('id', 'classes')
            ->setAttribute('group', false)
            ->setAttribute('required', false);

        $this->add($this->classesElement);

        $vtsClassesInput = new Input(self::FIELD_CLASSES);
        $vtsClassesInput->setRequired(false);

        $inputFilter = new InputFilter();
        $inputFilter->add($vtsClassesInput);

        $this->setInputFilter($inputFilter);
    }

    public function getClassesElement()
    {
        return $this->classesElement;
    }
}
