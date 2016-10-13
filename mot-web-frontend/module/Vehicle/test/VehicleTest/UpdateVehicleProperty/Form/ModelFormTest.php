<?php
namespace VehicleTest\UpdateVehicleProperty\Form;

use DvsaCommon\ApiClient\Vehicle\Dictionary\Dto\ModelDto;
use Vehicle\UpdateVehicleProperty\Form\ModelForm;

class ModelFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModelForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new ModelForm($this->getModelList());
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
            ModelForm::FIELD_MODEL_NAME => 1,
            ModelForm::FIELD_OTHER_MODEL_NAME => "other model"
        ];

        $this->form->setData($formData);
        $isValid = $this->form->isValid();

        $this->assertTrue($isValid);
        $this->assertNull($this->form->getOtherModelElement()->getValue());
    }

    public function testSelectedModelNameReturnsCorrectValue()
    {
        $formData = [
            ModelForm::FIELD_MODEL_NAME => 1,
            ModelForm::FIELD_OTHER_MODEL_NAME => ""
        ];

        $this->form->setData($formData);
        $isValid = $this->form->isValid();

        $this->assertTrue($isValid);
        $this->assertEquals("A1", $this->form->getSelectedModelName());
    }

    public function validData()
    {
        return [
            [
                [
                    ModelForm::FIELD_MODEL_NAME => 1,
                    ModelForm::FIELD_OTHER_MODEL_NAME => ""
                ]
            ],
            [
                [
                    ModelForm::FIELD_MODEL_NAME => ModelForm::OTHER_ID,
                    ModelForm::FIELD_OTHER_MODEL_NAME => "other model"
                ]
            ],
            [
                [
                    ModelForm::FIELD_MODEL_NAME => ModelForm::OTHER_ID,
                    ModelForm::FIELD_OTHER_MODEL_NAME => $this->generateOtherModelName(ModelForm::MAX_OTHER_MODEL_LENGTH)
                ]
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
                    ModelForm::FIELD_MODEL_NAME => 999,
                    ModelForm::FIELD_OTHER_MODEL_NAME => ""
                ]
            ],
            [
                [
                    ModelForm::FIELD_MODEL_NAME => ModelForm::OTHER_ID,
                    ModelForm::FIELD_OTHER_MODEL_NAME => ""
                ]
            ],
            [
                [
                    ModelForm::FIELD_MODEL_NAME => ModelForm::OTHER_ID,
                    ModelForm::FIELD_OTHER_MODEL_NAME => $this->generateOtherModelName(ModelForm::MAX_OTHER_MODEL_LENGTH + 1)
                ]
            ],
            [
                [
                    ModelForm::FIELD_MODEL_NAME => "",
                    ModelForm::FIELD_OTHER_MODEL_NAME => ""
                ]
            ],
        ];
    }

    private function getModelList()
    {
        return [
            (new ModelDto())->setId(1)->setName("A1"),
            (new ModelDto())->setId(2)->setName("A2"),
            (new ModelDto())->setId(3)->setName("A3"),
            (new ModelDto())->setId(4)->setName("A4"),
            (new ModelDto())->setId(ModelForm::OTHER_ID)->setName(ModelForm::OTHER_NAME),
        ];
    }

    private function generateOtherModelName($length)
    {
        return str_repeat("x", $length);
    }
}
