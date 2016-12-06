<?php

namespace VehicleTest\CreateVehicle\Form;

use Vehicle\CreateVehicle\Form\ModelForm;

class ModelFormTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenUserSelectsPleaseSelect_validationMessageIsShown()
    {
        $form = new ModelForm($this->mockModels());

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
        $form = new ModelForm($this->mockModels());

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
        $form = new ModelForm($this->mockModels(), 'Other');

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => 'X1',
        ];

        $form->setData($modelData);
        $this->assertTrue($form->isValid());
        $this->assertEmpty($form->getOther()->getMessages());
    }

    public function testWhenUserEntersInValidOtherData_validationWillFail()
    {
        $form = new ModelForm($this->mockModels(), 'Other');

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => 'Something-Invalid',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
    }

    public function testWhenUserEntersInValidOtherDataWithNoCharacters_validationWillFail()
    {
        $form = new ModelForm($this->mockModels(), 'Other');

        $modelData = [
            ModelForm::MODEL => 'Other',
            ModelForm::OTHER => '',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
    }


    private function mockModels()
    {
        return [
            ['id' => '1111', 'name' => 'A1'],
            ['id' => '1234', 'name' => 'A2'],
        ];
    }
}