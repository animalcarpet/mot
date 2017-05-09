<?php

namespace VehicleTest\UpdateVehicleProperty\Form;

use Vehicle\UpdateVehicleProperty\Form\ModelForm;
use Vehicle\UpdateVehicleProperty\Form\OtherModelForm;

class OtherModelFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     *
     * @param array $formData
     */
    public function testIsValidReturnsTrueForValidData($isOtherModelRequired, array $formData)
    {
        $form = new OtherModelForm($isOtherModelRequired);
        $form->setData($formData);
        $isValid = $form->isValid();
        $this->assertTrue($isValid);
    }

    public function testSelectedModelNameReturnsCorrect()
    {
        $formData = [
            OtherModelForm::FIELD_OTHER_MODEL_NAME => 'other model',
        ];

        $form = new OtherModelForm();
        $form->setData($formData);
        $isValid = $form->isValid();

        $this->assertTrue($isValid);
        $this->assertEquals(ModelForm::FIELD_OTHER_MODEL_NAME, $form->getSelectedModelName());
    }

    public function validData()
    {
        return [
            [
                true,
                [
                    OtherModelForm::FIELD_OTHER_MODEL_NAME => 'other model',
                ],
            ],
            [
                false,
                [
                    OtherModelForm::FIELD_OTHER_MODEL_NAME => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidData
     *
     * @param array $formData
     */
    public function testIsValidReturnsFalseForInvalidData($isOtherModelRequired, array $formData)
    {
        $form = new OtherModelForm($isOtherModelRequired);
        $form->setData($formData);
        $isValid = $form->isValid();

        $this->assertFalse($isValid);
    }

    public function invalidData()
    {
        return [
            [
                true,
                [
                    OtherModelForm::FIELD_OTHER_MODEL_NAME => '',
                ],
            ],
            [
                true,
                [
                    OtherModelForm::FIELD_OTHER_MODEL_NAME => $this->generateOtherModelName(OtherModelForm::MAX_OTHER_MODEL_LENGTH + 1),
                ],
            ],
            [
                false,
                [
                    OtherModelForm::FIELD_OTHER_MODEL_NAME => $this->generateOtherModelName(OtherModelForm::MAX_OTHER_MODEL_LENGTH + 1),
                ],
            ],
        ];
    }

    private function generateOtherModelName($length)
    {
        return str_repeat('x', $length);
    }
}
