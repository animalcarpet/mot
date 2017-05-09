<?php

namespace VehicleTest\UpdateVehicleProperty\Form;

use DvsaCommon\Dto\Vehicle\MakeDto;
use Vehicle\UpdateVehicleProperty\Form\MakeForm;

class MakeFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MakeForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new MakeForm($this->getMakeList());
    }

    /**
     * @dataProvider validData
     *
     * @param array $formData
     */
    public function testIsValidReturnsTrueForValidData($formData)
    {
        $this->form->setData($formData);
        $isValid = $this->form->isValid();
        $this->assertTrue($isValid);
    }

    public function testWhenModelIsSelectedThenOtherModelFieldIsIgnored()
    {
        $formData = [
            MakeForm::FIELD_MAKE_NAME => 1,
            MakeForm::FIELD_OTHER_MAKE_NAME => 'other model',
        ];

        $this->form->setData($formData);
        $isValid = $this->form->isValid();

        $this->assertTrue($isValid);
        $this->assertNull($this->form->getOtherMAKEElement()->getValue());
    }

    public function testSelectedModelNameReturnsCorrectValue()
    {
        $formData = [
            MakeForm::FIELD_MAKE_NAME => 1,
            MakeForm::FIELD_OTHER_MAKE_NAME => '',
        ];

        $this->form->setData($formData);
        $isValid = $this->form->isValid();

        $this->assertTrue($isValid);
        $this->assertEquals('Audi', $this->form->getSelectedMakeName());
    }

    public function validData()
    {
        return [
            [
                [
                    MakeForm::FIELD_MAKE_NAME => 1,
                    MakeForm::FIELD_OTHER_MAKE_NAME => '',
                ],
            ],
            [
                [
                    MakeForm::FIELD_MAKE_NAME => MakeForm::OTHER_ID,
                    MakeForm::FIELD_OTHER_MAKE_NAME => 'other model',
                ],
            ],
            [
                [
                    MakeForm::FIELD_MAKE_NAME => MakeForm::OTHER_ID,
                    MakeForm::FIELD_OTHER_MAKE_NAME => $this->generateOtherModelName(MakeForm::MAX_OTHER_MAKE_LENGTH),
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidData
     *
     * @param array $formData
     */
    public function testIsValidReturnsFalseForInvalidData($formData)
    {
        $this->form->setData($formData);
        $isValid = $this->form->isValid();
        $this->assertFalse($isValid);
    }

    public function invalidData()
    {
        return [
            [
                [
                    MakeForm::FIELD_MAKE_NAME => 999,
                    MakeForm::FIELD_OTHER_MAKE_NAME => '',
                ],
            ],
            [
                [
                    MakeForm::FIELD_MAKE_NAME => MakeForm::OTHER_ID,
                    MakeForm::FIELD_OTHER_MAKE_NAME => '',
                ],
            ],
            [
                [
                    MakeForm::FIELD_MAKE_NAME => MakeForm::OTHER_ID,
                    MakeForm::FIELD_OTHER_MAKE_NAME => $this->generateOtherModelName(MakeForm::MAX_OTHER_MAKE_LENGTH + 1),
                ],
            ],
            [
                [
                    MakeForm::FIELD_MAKE_NAME => '',
                    MakeForm::FIELD_OTHER_MAKE_NAME => '',
                ],
            ],
        ];
    }

    private function getMakeList()
    {
        return [
            (new MakeDto())->setId(1)->setName('Audi'),
            (new MakeDto())->setId(2)->setName('BMW'),
            (new MakeDto())->setId(3)->setName('Citroen'),
            (new MakeDto())->setId(4)->setName('Daimler'),
            (new MakeDto())->setId(MakeForm::OTHER_ID)->setName(MakeForm::OTHER_NAME),
        ];
    }

    private function generateOtherModelName($length)
    {
        return str_repeat('x', $length);
    }
}
