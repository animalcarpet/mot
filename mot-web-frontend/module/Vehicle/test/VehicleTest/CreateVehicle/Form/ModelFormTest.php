<?php

namespace VehicleTest\CreateVehicle\Form;

use Vehicle\CreateVehicle\Form\ModelForm;

class ModelFormTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenUserSelectsPleaseSelect_validationMessageIsShown()
    {
        $form = new ModelForm($this->getMockModels());

        $modelData = [
            ModelForm::MODEL => 'Please select',
            ModelForm::OTHER => '',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getModel()->getMessages());
    }

    public function testWhenUserSelectsOtherButEmpty_validationMessageIsShown()
    {
        $form = new ModelForm($this->getMockModels());

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => '',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getOther()->getMessages());
    }

    public function testWhenUserEntersValidOtherData_validationPasses()
    {
        $form = new ModelForm($this->getMockModels(), 'Other');

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => 'X1',
        ];

        $form->setData($modelData);
        $this->assertTrue($form->isValid());
        $this->assertEmpty($form->getOther()->getMessages());
    }

    public function testWhenUserEntersOtherOverMaxLengthOfCharacters_validationWillFail()
    {
        $form = new ModelForm($this->getMockModels(), 'Other');

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => 'TprWULx4hPzeKwHpPLyKZh48CA7EIPq68nNscASf',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
    }

    private function getMockModels()
    {
        return [
            ['id' => '1111', 'name' => 'A1'],
            ['id' => '1234', 'name' => 'A2'],
        ];
    }
}