<?php
namespace DvsaMotTest\Form;


use DvsaCommon\Messages\Vehicle\CreateVehicleErrors;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\I18n\Validator\Alnum;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class VrmUpdateForm extends Form
{
    const FIELD_VRM = "vrm";
    const LABEL = "Registration";
    const MESSAGE_IS_EMPTY = "you must enter VRM";

    public function __construct()
    {
        parent::__construct();

        $vrm = (new Text())
            ->setName(self::FIELD_VRM);
        $this->add($vrm);

        $this->setInputFilter($this->createInputFilter());
    }

    private function createInputFilter()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => self::FIELD_VRM,
            'required' => false,
            'validators' => [
                [
                    'name' => Alnum::class,
                    'options' => [
                        'messages' => [
                            Alnum::INVALID => self::LABEL . ' - ' . CreateVehicleErrors::REG_INVALID,
                            Alnum::NOT_ALNUM => self::LABEL . ' - ' . CreateVehicleErrors::REG_INVALID,
                        ]
                    ],
                ],
            ],
        ]);

        return $inputFilter;
    }
}
